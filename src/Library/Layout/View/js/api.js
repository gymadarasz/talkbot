"use strict";

class Ajax {
    getXMlHttpRequest(onSuccess, onError)
    {
        if (window.XMLHttpRequest) {
            var xhttp = new XMLHttpRequest();
        } else {
            // code for old IE browsers
            var xhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xhttp.onreadystatechange = () => {
            if (xhttp.readyState == 4) {
                if (xhttp.status == 200) {
                    onSuccess(xhttp);
                    return;
                }
                if (onError) {
                    onError(xhttp);
                    return;
                }
                console.error('Ajax error', xhttp);
            }
        };
        return xhttp;
    }

    send(method, target, onSuccess, onError, data)
    {
        var xhttp = this.getXMlHttpRequest(onSuccess, onError);
        xhttp.open(method, target, true);
        xhttp.send(data);
    }

    sendSync(method, target, onSuccess, onError, data)
    {
        var xhttp = this.getXMlHttpRequest(onSuccess, onError);
        xhttp.open(method, target, false);
        xhttp.send(data);
    }

    post(target, data, onSuccess, onError)
    {
        this.send('POST', target, onSuccess, onError, data);
    }

    get(target, onSuccess, onError)
    {
        this.send('POST', target, onSuccess, onError);
    }

    postSync(target, data, onSuccess, onError)
    {
        this.sendSync('POST', target, onSuccess, onError, data);
    }

    getSync(target, onSuccess, onError)
    {
        this.sendSync('POST', target, onSuccess, onError);
    }
}

class Api {
    constructor(ajax)
    {
        this.ajax = ajax;
        this.msgtpl = `
            <div class="alert alert-{{ class }} alert-dismissible fade show" role="alert">
                {{ message }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        this.base = document.querySelector('base').getAttribute('href') + '/src/api.php?q=';
    }

    serialize(obj, prefix)
    {
        var str = [], p;
        for (p in obj) {
            if (obj.hasOwnProperty(p)) {
                var k = prefix ? prefix + "[" + p + "]" : p,
                    v = obj[p];
                str.push(
                    (v !== null && typeof v === "object") ?
                        this.serialize(v, k) :
                        encodeURIComponent(k) + "=" + encodeURIComponent(v)
                );
            }
        }
        return str.join("&");
    }

    getFormData(form)
    {
        var data = [];
        var formData = new FormData(form);
        for (var key of formData.keys()) {
            data.push({ 'key': key });
        }
        var i = 0;
        for (var value of formData.values()) {
            data[i]['value'] = value;
            i++;
        }
        var results = {};
        data.forEach((elem) => { results[elem.key] = elem.value; });
        return results;
    }

    submit(button, route)
    {
        var form = button.closest('form');
        var data = new FormData(form);
        this.ajax.post(this.base + route, data, (xhttp) => {
            var resp = JSON.parse(xhttp.responseText);
            document.querySelectorAll('input[name=csrf]').forEach((elem) => {
                elem.value = resp.csrf;
            });
            console.log(resp);
            var msgs = form.querySelector('.messages');
            msgs.innerHTML = '';
            if (resp.messages) { // TODO: !@# error message type shown and works add the rests (see in Messages.php and bootstrap classes at https://getbootstrap.com/docs/4.5/components/alerts/, also add input helper messages see more at api response and at https://getbootstrap.com/docs/4.5/components/forms/#help-text) 
                const bsalerts = {
                    'error': 'danger',
                };
                for (var key in resp.messages) {
                    resp.messages[key].forEach((message) => {
                        msgs.innerHTML += this.msgtpl
                            .replace('{{ class }}', bsalerts[key] ? bsalerts[key] : key)
                            .replace('{{ message }}', message);
                    });
                }
            }
            form.querySelectorAll('.invalid-feedback').forEach((elem) => {
                elem.style.display = 'none';
            });
            if (resp.errors) {
                for (var key in resp.errors) {
                    var msg = resp.errors[key].join(', ');
                    var feedback = form.querySelector('#' + key + '-feedback.invalid-feedback');
                    feedback.innerHTML = msg;
                    feedback.style.display = 'block';
                }
            }
        });
    }
}

var api = new Api(new Ajax());