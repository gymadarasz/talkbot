$(
    function () {
        var apiBase = $('base').attr('href') + '/src/api.php';
        $('#registryButton').click(
            function () {
                var formData = $('#registryForm').serializeArray();
                var data = {};
                $(formData).each(
                    function (i, e) {
                        data[e.name] = e.value;
                    }
                );
                $.post(
                    apiBase + '?q=registry', data, function (resp) {
                        console.log(resp);
                    }
                );
            }
        );
    }
);

