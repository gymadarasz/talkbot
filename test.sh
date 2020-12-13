error_exit() {
    echo ""
    echo "TESTS FAILED"
    exit 1
}

success_exit() {
    echo ""
    echo "TESTS PASSED"
    exit 0
}

show_next() {
    echo ""
    echo "====================================================================="
    echo "-- [ $1 ]"
    echo "====================================================================="
    echo ""
}

trap error_exit 0 

show_next "clean up.."
./cleanup.sh

show_next "php-cs-fixer"
vendor/bin/php-cs-fixer fix src
vendor/bin/php-cs-fixer fix tests

show_next "csfix.php"
php csfix/csfix.php src
php csfix/csfix.php tests

show_next "phpcbf"
vendor/bin/phpcbf src --ignore=*.js
vendor/bin/phpcbf tests

set -e

show_next "phpcs"
vendor/bin/phpcs src --ignore=*.js
vendor/bin/phpcs tests

show_next "phpstan"
vendor/bin/phpstan analyse --level 8 src
vendor/bin/phpstan analyse --level 8 tests

show_next "phan"
vendor/bin/phan

show_next "phpmd"
vendor/bin/phpmd src,tests text cleancode,codesize,controversial,design,naming,unusedcode

show_next "test.php"
php src/Library/test.php

trap success_exit 0