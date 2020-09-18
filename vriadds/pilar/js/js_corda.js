// #
// # VRI - Universidad Nacional del Altiplano - Puno 2020
// # Ing. Ramiro Pedro, Ing. Fred Torres, Ing. Julio Tisnado
// #

function lodPanel(id,ctrl)
{
	jVRI("#"+id).load(ctrl);
}
function jsCarrer(value){
	jVRI("#panelCarrer").load("cordinads/setCarrera/"+value);
}
function jsLoadModalCord(val,ctrl){
	jVRI("#mdlContCord").html();
	$("#cordModal").modal("show");
	jVRI("#mdlContCord").load(ctrl+val);
}
function jsCordDocInfo(val){
	jVRI("#mdlContCord").html(" ");
	$("#cordModal").modal("show");
	jVRI("#mdlContCord").load("cordinads/jsmdlDocInfo/"+val);
}
function jsCordDocOpt(val){
	jVRI("#mdlContCord").html(" ");
	$("#cordModal").modal("show");
	jVRI("#mdlContCord").load("cordinads/jsmdlDocOpc/"+val);
}
function LoadForm(dond,funti,idis){
	// alert(idis.name);
	jVRI("#"+dond).load(funti, new FormData(idis));
}

function jsLoadConfirmLin(val){
	jVRI("#mdlContCord").html(" ");
	$("#cordModal").modal("show");
	jVRI("#mdlContCord").load("cordinads/vwMdlUpdateLin/"+val);
}


function jsConfirmlLinea(val){
	$("#cordModal").modal("show");
    jVRI("#estado"+val).html( "Grabando...");
    jVRI.ajax({
        url  : "cordinads/execUpdateLin/"+val,
        data :  new FormData(porquee),
        success: function( arg )
        {
            $("#cordModal").modal("hide");
            jVRI("#estado"+val).html( arg );
            jVRI("#panelCord").load('cordinads/vwValidaLineas');
        }
    });
}

function pyDirect( itm, tram )
{
	// alert(tram);
    if( confirm('Este proyecto de tesis sera enviado al Director, desea continuar?') ) {
    	
    	jVRI("#respcord").load("cordinads/listPyDire/"+tram);

    	// jVRI("#item"+itm).load("cordinads/listPyDire/"+tram);
        $("#item"+itm ).fadeOut(); //hide();
        // jVRI("#panelCord").load( "cordinads/vwProyectos/"+tram );
    }
}

function jsRevalidaLinea(val){
	jVRI("#indecisosisi").load( "cordinads/vwMdlValidaLinea/"+val);
	// $
	// jVRI.ajax({
	// 	url  : "pilar/cordinads/execUpdateLin/"+val+"/"+doc,
	// 	data : new FormData(frmoti),
	// 	success: function( arg )
	// 	{
	// 		jVRI("#pmsg").html( "" );
	// 		jVRI("#pdta").html( arg );
	// 	}
	// }); 
}


function popExeRechaza(val){
	// $("#cordModal").modal("show");
    // datita = $("#corazon").serialize();
       datita = new FormData(corazon);
    jVRI("#popis").html( "Grabando...");
    // data.append("webmasterfile", "freed");
    jVRI.ajax({
        url  : "cordinads/doRechaza/"+val,
        data :  datita ,
        success: function( arg )
        {
            // $("#cordModal").modal("hide");
            jVRI("#popis").html( arg );
            // jVRI("#panelCord").load('cordinads/vwValidaLineas');
        }
    });
}
