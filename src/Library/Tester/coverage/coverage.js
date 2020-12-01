ul_filelist = document.getElementById('filelist');
for(var file in files) {

    li_file = document.createElement('li');
    li_file.onclick = function () {
        if (this.classList.contains('open')) {
            this.classList.remove('open');
            this.classList.add('closed');
        } else {
            this.classList.add('open');
            this.classList.remove('closed');
        }
    }
        b_percentage = document.createElement('b');
        b_percentage.innerHTML = parseFloat(files[file].percentage).toFixed(2) + ' %';
        li_file.appendChild(b_percentage);

        i_file = document.createElement('i');
        i_file.innerHTML = file;
        li_file.appendChild(i_file);


        ul_lines = document.createElement('ul');
    for(var line in files[file].lines) {
        li_line = document.createElement('li');
        li_line.setAttribute('class', 'info' + files[file].lines[line].info);
            b_lno = document.createElement('b');
            b_lno.innerHTML = (parseInt(line)+1) + ':';
            li_line.appendChild(b_lno);

            pre_code = document.createElement('pre');
            pre_code.innerHTML = files[file].lines[line].code;
            li_line.appendChild(pre_code);
        ul_lines.appendChild(li_line);
    }
        li_file.appendChild(ul_lines);
    ul_filelist.appendChild(li_file);
}


