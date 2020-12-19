"use strict";

class Tpls {
    getMessage(clazz, message) {        
        return `
            <div class="alert alert-{{ class }} alert-dismissible fade show" 
                role="alert">
                {{ message }}
                <button type="button" class="close" data-dismiss="alert" 
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `
        .replaceAll('{{ class }}', clazz)
        .replaceAll('{{ message }}', message);
    }
      
    getSpinnerBorder(size = 1) {
        return `
            <div class="spinner-border" 
                style="width: {{ size }}rem; height: {{ size }}rem;" 
                role="status">
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
    
    getActionLink(action, row) {
        return this.replace(`
            <a href="{{ href }}" title="{{ title }}">{{ text }}</a>
        `, this.replaceKeys(action, row));
    }
    
    getActionTick(action, row) {
        var result = this.replaceKeys(action, row);
        return `
            {{ text }}
        `.replaceAll('{{ text }}', result.text[result.value]);
    }
    
    replaceKeys(keysData, valuesData) {
        var result = {...keysData};
        for (var key in result) {
            if (typeof result[key] === 'string') {
                result[key] = this.replace(result[key], valuesData);
            }
        }
        return result;
    }
    
    replace(tpl, data) {
        var ret = tpl;
        for (var key in data) {
            ret = ret.replaceAll('{{ ' + key + ' }}', data[key]);
        }
        return ret;
    }
}

class Ajax {
    constructor() {
        this.base = document.querySelector('base').getAttribute('href') + 
                '/src/api.php?q=';
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
        var msgs = form.querySelector('.messages');
        msgs.innerHTML = '';
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
        var columsData = this.getColumsData();
        this.api.get(this.getTable(), this.getEndPoint(), {
            csrf: this.getCsrf(),
            fields: columsData.fields.join(',')
        }, (resp) => {
            this.clearRows();
            console.log('list response:', resp);
            var tableRows = [];
            if (resp.rows) {
                resp.rows.forEach((row) => {
                    tableRows.push(this.dbToTableRow(row, columsData.actions));
                });
            }
            this.getBody().innerHTML = tableRows.join('');
        });
    }
    
    dbToTableRow(row, actions) {
        var cells = '';
        this.getColumns().forEach((col, i) => {
            cells += this.tpls.getTableCell(1, col ? 
                row[col] : (actions[i] ? 
                    this.getActions(row, actions[i]) : '&nbsp;'));
        });
        return this.tpls.getTableRow(cells);
    }
    
    getActions(row, actions) {
        console.log(row, actions);
        var actionsHtml = '';
        actions.forEach((action) => {
            switch (action.type) {
                case 'link':
                    actionsHtml += this.tpls.getActionLink(action, row);
                    break;
                case 'tick':
                    actionsHtml += this.tpls.getActionTick(action, row);
                    break;
                default:
                    throw ['Invalid action type', action];
            }
        });
        return actionsHtml;
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
            this.header = document.querySelectorAll(
                    this.selector + ' thead tr th');
        }
        return this.header;
    }
    
    getColumsData() {
        var fields = [];
        var actions = [];
        this.getHeader().forEach((head) => {
            var field = head.getAttribute('data-field').trim();
            var dataActions = head.getAttribute('data-actions').trim();
            var rowActions = dataActions ? JSON.parse(dataActions) : null;
            if (field) {
                if (!fields.includes(field)) {
                    fields.push(field);
                }
            }
            if (rowActions) {
                rowActions.forEach((rowAction) => {
                    rowAction.fields.forEach((field) => {
                        if (!fields.includes(field)) {
                            fields.push(field);
                        }
                    });
                });
            }
            actions.push(rowActions);
        });
        return {fields, actions};
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
        this.ajax.get(
                this.ajax.base  + route + '&' + this.serialize(data), 
                (xhttp) => {
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
                var feedback = form.querySelector(
                        '#' + key + '-feedback.invalid-feedback');
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

class App {
    constructor() {
        try {
            var tpls = new Tpls();
            var ajax = new Ajax();
            var form = new Form(ajax, tpls);
            var list = new List(ajax, tpls);
            this.api = new Api(ajax, tpls, form, list);
        } catch (e) {
            this.handleError(e);
        }
    }
    
    loadList(selector) {
        try {
            return this.api.list.load(selector);
        } catch (e) {
            this.handleError(e);
        }
    }
    
    submitForm(button, route) {
        try {
            return this.api.form.submit(button, route);
        } catch (e) {
            this.handleError(e);
        }
    }
    
    handleError(e) {
        console.error('Javascript error:' , e);
        if (this.api.tpls) {
            var messages = document.querySelector('.messages');
            if (messages) {
                messages.innerHTML += this.api.tpls.getMessage(
                        'danger', 'An application error happened');
            }
        }
        if (this.api) {
            this.api.get(document, 'error', {
                error: e, 
                trace: (new Error()).stack
            });
        }
    }
}

var app = new App();