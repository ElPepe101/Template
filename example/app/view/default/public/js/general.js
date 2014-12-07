
/** ----------------------------------------------------------------------------------
 * -----------------------------------------------------------------------------------
 * Setup
 */

var system_url = ['stabilizat.com/portal/', 'www.stabilizat.com/portal/', 'localhost/stabilizat.com/portal/'];
var gURLs = new Object();
gURLs.template = 'app/views/default/';
gURLs.patients = 'pacientes';
gURLs.searchPatient = 'pacientes/search';

gURLs.newAppointment = 'pacientes/search';
gURLs.setAppointment = 'pacientes/setAppointment';
gURLs.changeAppointment = 'pacientes/changeAppointment';
gURLs.resetAppointment = 'pacientes/resetAppointment';

gURLs.newPatientLocationSelect = 'nuevo_paciente/locationSelect';
gURLs.products = 'productos/listInventory';
gURLs.repCalendar = 'calendario';

gURLs.vending = 'productos/vending';
gURLs.receipt = 'productos/recibo';

gURLs.sales = 'perfil/venta';
gURLs.checkEmail = 'perfil/checkEmail';
gURLs.checkUser = 'perfil/checkUser';

//gURLs.editUser = 'usuarios/editar';
//gURLs.newUser = 'usuarios/nuevo';

gURLs.newUserLocationSelect = 'usuarios/locationSelect';
gURLs.userCheckEmail = 'usuarios/checkEmail';
gURLs.userCheckUser = 'usuarios/checkUser';

gURLs.report_dates = 'reportes/citas';
gURLs.report_vending = 'reportes/ventas';

gURLs.shipment = 'productos/solicita';

/** ----------------------------------------------------------------------------------
 * -----------------------------------------------------------------------------------
 * ROUTER - DO NOT MOVE
 */

//console.log(window.location.protocol + '/' + window.location.pathname + window.location.search);

var complete_path = window.location.hostname + window.location.pathname;
_.each(system_url, function(v, k) {
	if( complete_path.indexOf(v) > -1 ) {
		system_url = window.location.protocol + '//' + v ;
	}
});

_.each(gURLs, function(v, k) {
	gURLs[k]= system_url + v;
});

/** ----------------------------------------------------------------------------------
 * -----------------------------------------------------------------------------------
 * General workflow
 */
$('form input, form select').focus(function() {
	if($(this).val() == $(this).attr('data-value'))
		$(this).val('');
}).blur(function(){
	if($(this).val() == '') {
		$(this).val($(this).attr('data-value'));
		$(this).removeClass('edited');
	} else {
		$(this).addClass('edited');
	}
});

$('form input, form select').each(function(k, v) {
	if( !!$(v).attr('required') ) {
		var $label = $(v).prev();
		$label.text($label.text()+' *');
	}
});

/** ----------------------------------------------------------------------------------
 * -----------------------------------------------------------------------------------
 * General functions
 */

// http://stackoverflow.com/questions/4878756/javascript-how-to-capitalize-first-letter-of-each-word-like-a-2-word-city
// http://stackoverflow.com/questions/15150168/title-case-in-javascript-for-diacritics-non-ascii
String.prototype.titlelize = function(){
	return this.replace(/[^-'\s]+/g, function(word) {
		//return word.replace(/^./, function(first) {
			return word.charAt(0).toUpperCase() + word.substr(1).toLowerCase();
		//});
	});
};

$('.inline.optional input[type="text"]').change(function(){
	
	if($(this).val() != $(this).attr('data-value') && $(this).parent().find('input[type="checkbox"]:checked').length == 0) {
		$(this).parent().find('input[type="checkbox"]').attr('checked','checked');
	} else {
		$(this).parent().find('input[type="checkbox"]').removeAttr('checked');
	}
});

////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
// PAYMENT PROCESS ////////////////////////////////////////////////////////////////////////
var jqxhr = $.getJSON();
var search_value = '';
var products = {};

// si existe forma de vender hacemos la petición
if($('nav.module a[href="'+system_url+'perfil"]').text()=='Perfil'){
	jqxhr = $.getJSON(gURLs.products+'/json');
}

jqxhr.done(function(response){

	products = response;
	setTotal();
	changeQty();
	removeBtn();
	createOptions(response);
	
	$('.add').click(function(event){
		event.preventDefault();
	
		$('.venta tbody').append($('.template').val());
		changeQty();
		removeBtn();
		createOptions(response);
	}); 
});
	
jqxhr.fail(function(){
	console.log('error');
	return false;
});

var setTotal = function() {

	var total = 0;
	$('.venta tbody td:nth-child(4)').each(function(k,v){
		total += Number($(v).html().replace('$ ','').replace('.00',''));
	});
	$('.pagos tbody td:nth-child(4)').each(function(k,v){
		total += Number($(v).html().replace('$ ','').replace('.00',''));
	});
	$('.total').text('$ '+total+'.00');

};

var changeQty = function () {

	$('.qty').unbind('change').change(function(){
		var u_val = Number($(this).parent().prev().text().replace(' ','').replace('$','').replace('.00',''));
		$(this).parent().next().text('$ '+(u_val * $(this).val())+'.00');
		setTotal();
	});
};

var removeBtn = function() {

	$('.remove').unbind('click').click(function(){
		$.when($(this).parents('tr').remove()).then(function(){

			if($('.venta tbody tr').length == 0){
				$('.venta tbody').append($('.template').val());
			}
		});
	});
};

var createOptions = function(response) {

	var options = ''; 
	_.each(response.names,function(v,k){
		options += "\t<option value='"+v+"'>"+v+"</option>\n";
	});

	$('.new').append(options);

	$('.new').unbind('change').change(function(event){

		$(this).parent().next().text('$ '+response.data[$(this).val()].price+'.00');
		$(this).parent().next().next().find('.qty').val(1).trigger('change');
		$(this).parent().next().next().find('.type').val(response.data[$(this).val()].type);
	});
};

var delay = (function(){
	var timer = 0;
	return function(callback, ms){
		clearTimeout (timer);
		timer = setTimeout(callback, ms);
	};
})();

// Taken from
// http://snipplr.com/view/29911/javascript-alert-and-confirm-dialog-the-fancybox-way/
function fancyAlert(msg) {

	jQuery.fancybox({
		'modal' : true,
		'content' : "<div style=\"margin:1px;width:240px;\">"+msg+"<div style=\"text-align:right;margin-top:10px;\"><input style=\"margin:3px;padding:0px;\" type=\"button\" onclick=\"jQuery.fancybox.close();\" value=\"Ok\"></div></div>"
	});
}

function fancyConfirm(msg,callback) {
	
	var ret = false;
	jQuery.fancybox({
		maxWidth	: 460,
		//maxHeight	: 220,
		fitToView	: false,
		width		: '70%',
		height		: '40%',
		autoSize	: false,
		modal 		: true,
		type 		: 'html',
		content : '<div>'+msg+'<br /><div class="fancyConfirm_buttons"><input id="fancyConfirm_cancel" type="button" value="Cancelar"><input id="fancyConfirm_ok"type="button" value="Continuar"></div></div>',
		afterShow : function(current, previous) {
	
			$("#fancyConfirm_cancel").click(function() {
				ret = false;
				jQuery.fancybox.close();
			});
			
			$("#fancyConfirm_ok").click(function() {
				ret = true;
				jQuery.fancybox.close();
			});			
		},
		afterClose : function() {
			
			callback(this, ret);
			//callback.call(this, ret);
		}
	});
}