"use strict";

class Tpls {
    getMessage(clazz, message) {        
        return `
            <div class="alert alert-{{ class }} alert-dismissible fade show" role="alert">
                {{ message }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `
        .replaceAll('{{ class }}', clazz)
        .replaceAll('{{ message }}', message);
    }
      
    getSpinnerBorder(size = 1) {
        return `
            <div class="spinner-border" style="width: {{ size }}rem; height: {{ size }}rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        `
        .replaceAll('{{ size }}', size);
    }
    
    getSpinner(size = 1) {
        return `
            <div class="text-center">
                {{ spinnerBorder }}
            </div>
        `
        .replaceAll('{{ spinnerBorder }}', this.getSpinnerBorder(size));
    }
    
    getTableCell(colspan, content) {
        return `
            <td colspan="{{ colspan }}">{{ content }}</td>
        `
        .replaceAll('{{ colspan }}', colspan)
        .replaceAll('{{ content }}', content);
    }
    
    getTableRow(cells) {
        return `
            <tr>
                {{ cells }}
            </tr>
        `
        .replaceAll('{{ cells }}', cells);
    }
    
    
}

class Ajax {
    constructor() {
        this.base = document.querySelector('base').getAttribute('href') + '/src/api.php?q=';
    }
    
    getXMlHttpRequest(onSuccess, onError)
    {
        if (window.XMLHttpRequest) {
            var xhttp = new XMLHttpRequest();
        } else {
            // code for old IE browsers
            var xhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xhttp.onreadystatechange = () => {
            if (xhttp.readyState === 4) {
                if (xhttp.status === 200) {
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
        this.send('GET', target, onSuccess, onError);
    }

    postSync(target, data, onSuccess, onError)
    {
        this.sendSync('POST', target, onSuccess, onError, data);
    }

    getSync(target, onSuccess, onError)
    {
        this.sendSync('GET', target, onSuccess, onError);
    }
}

class Form {
    constructor(ajax, tpls) {
        this.ajax = ajax;
        this.tpls = tpls;
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
        var btnOrigHTML = button.innerHTML;
        button.setAttribute('disabled', 'disabled');
        button.innerHTML = this.tpls.getSpinnerBorder() + button.innerHTML;
        var form = button.closest('form');
        var data = new FormData(form);
        this.api.post(form, route, data, () => {
            button.innerHTML = btnOrigHTML;
            button.removeAttribute('disabled');
        });
    }
}

class List {
    constructor(ajax, tpls) {
        this.ajax = ajax;
        this.tpls = tpls;
        
        this.selector = null;
        this.csrf = null;
        this.table = null;
        this.header = null;
        this.body = null;
        this.endPoint = null;
    }
    
    load(selector) {
        this.selector = selector;
        this.clearRows();
        this.addRows([
            {
                colspan: this.getHeader().length,
                content: this.tpls.getSpinner(3)
            }
        ]);
        this.api.get(this.getTable(), this.getEndPoint(), {
            csrf: this.getCsrf(),
            fields: this.getFields().join(',')
        }, (resp) => {
            this.clearRows();
            console.log('list response:', resp);
            var tableRows = [];
            resp.rows.forEach((row) => {
                tableRows.push(this.dbToTableRow(row));
            });
            this.getBody().innerHTML = tableRows.join('');
        });
    }
    
    dbToTableRow(row) {
        var cells = '';
        this.getColumns().forEach((col) => {
            cells += this.tpls.getTableCell(1, col ? row[col] : '&nbsp;');
        });
        return this.tpls.getTableRow(cells);
    }
    
    getCsrf() {
        return this.getTable().querySelector('input[name=csrf]').value;
    }
    
    getEndPoint() {
        if (!this.endPoint) {
            this.endPoint = this.getTable().getAttribute('data-end-point');
        }
        return this.endPoint;
    }
    
    getTable() {
        if (!this.table) {
            this.table = document.querySelector(this.selector);
        }
        return this.table;
    }
    
    getHeader() {
        if (!this.header) {
            this.header = document.querySelectorAll(this.selector + ' thead tr th');
        }
        return this.header;
    }
    
    getFields() {
        var fields = [];
        this.getHeader().forEach((head) => {
            var field = head.getAttribute('data-field').trim();
            if (field) {
                fields.push(field);
            }
        });
        return fields;
    }
    
    getColumns() {
        var cols = [];
        this.getHeader().forEach((head) => {
            cols.push(head.getAttribute('data-field').trim());
        });
        return cols;
    }
    
    getBody() {
        if (!this.body) {
            this.body = document.querySelector(this.selector + ' tbody');
        }
        return this.body;
    }
    
    clearRows() {
        this.getBody().innerHTML = '';
    }
    
    addRows(rows) {
        rows.forEach((row) => {
            var cells = this.tpls.getTableCell(row.colspan, row.content);
            this.getBody().innerHTML += this.tpls.getTableRow(cells);
        });
    }
}

class Api {
    constructor(ajax, tpls, form, list) {        
        this.ajax = ajax;       
        this.tpls = tpls;
        this.form = form;
        this.list = list;
        
        this.form.api = this;
        this.list.api = this;
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
    
    get(form, route, data, onResponse, onRedirect, onError) {
        this.ajax.get(this.ajax.base  + route + '&' + this.serialize(data), (xhttp) => {
            this.handleAjaxResponse(form, xhttp, onResponse, onRedirect);
        }, onError);
    }
    
    post(form, route, data, onResponse, onRedirect, onError) {
        this.ajax.post(this.ajax.base + route, data, (xhttp) => {
            this.handleAjaxResponse(form, xhttp, onResponse, onRedirect);
        }, onError);
    }
    
    handleAjaxResponse(form, xhttp, onResponse, onRedirect) {
        var resp = JSON.parse(xhttp.responseText);
        if (typeof resp.redirect !== 'undefined') {
            if (onRedirect) {
                onRedirect(resp);
            }
            document.location.href = this.getWebBase() + resp.redirect;
            return;
        }
        document.querySelectorAll('input[name=csrf]').forEach((elem) => {
            elem.value = resp.csrf;
        });
        var msgs = form.querySelector('.messages');
        msgs.innerHTML = '';
        if (resp.messages) { // TODO: error message type shown and works add the rests (see in Messages.php and bootstrap classes at https://getbootstrap.com/docs/4.5/components/alerts/, also add input helper messages see more at api response and at https://getbootstrap.com/docs/4.5/components/forms/#help-text) 
            const bsalerts = {
                'error': 'danger'
            };
            for (var key in resp.messages) {
                resp.messages[key].forEach((message) => {
                    msgs.innerHTML += this.tpls.getMessage(
                        bsalerts[key] ? bsalerts[key] : key,
                        message
                    );
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
        if (onResponse) {
            onResponse(resp);
        }
    }

    getWebBase() {
        return document.querySelector('base').getAttribute('href') + '?q=';
    }
}

var app = {};
app.tpls = new Tpls();
app.ajax = new Ajax();
app.form = new Form(app.ajax, app.tpls);
app.list = new List(app.ajax, app.tpls);
var api = new Api(app.ajax, app.tpls, app.form, app.list);