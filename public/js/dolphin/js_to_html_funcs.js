/*
 *Author: Nicholas Merowsky
 *Date: 03 Apr 2015
 *Ascription:
 */

function createElement(type, fields, options){
	var element = document.createElement( type );
	for(var x = 0; x < fields.length; x++){
		if (fields[x] == 'OPTION') {
			var opt = document.createElement( 'option' );
				opt.value = options[x];
				opt.innerHTML = options[x];
				element.appendChild(opt);
		}else if (fields[x] == 'OPTION_DIS_SEL') {
			var opt = document.createElement( 'option' );
				opt.value = options[x];
				opt.innerHTML = options[x];
				opt.disabled = true;
				opt.selected = true;
				element.appendChild(opt);
		}else if(fields[x] == 'TEXTNODE'){
			element.appendChild(document.createTextNode(options[x]));
		}else{
			element.setAttribute(fields[x], options[x]);
		}
	}
	return element;
}

function createTidyDiv(size){
	var div = document.createElement( 'div' );
		div.setAttribute('class', 'col-md-'+size);
	return div;
}

function createLabeledDiv(size, label1, label2, element1, element2){
    var div = document.createElement( 'div' );
		div.setAttribute('class', 'text-center col-md-'+size);
        div.innerHTML = label1;
        div.appendChild(element1);
        div.innerHTML += '&nbsp;&nbsp;';
        div.appendChild(element2);
        div.innerHTML += label2;
	return div;
}

function mergeTidy(headDiv, size, elementList){
	for (var x = 0; x < elementList.length; x++) {
		var tidyDiv = createTidyDiv(size);
		for (var y = 0; y < elementList[x].length; y++) {
			tidyDiv.appendChild(elementList[x][y]);
		}
		headDiv.appendChild(tidyDiv);
	}
	return headDiv;
}

function scrollWindow(x, y){
	window.scrollBy(x, y);
}