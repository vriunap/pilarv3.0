
// #
// # VRI - Universidad Nacional de Ucayali - PUCALLPA 2020
// # Ing. Ramiro Pedro, Ing. Fred Torres, Ing. Julio Tisnado
// #

function showHidden(est){
	for (var i = 1; i < est; i++) {
		$('#est'+i).show();
	}
	$('#textdown').html("<a href='#'onclick='hiddenElem("+est+")'><p> Ocultar Pasos yá realizados</p><span class='glyphicon glyphicon-chevron-up'></span></a>");
}

function hiddenElem(esti){
	for (var i = 1; i < esti; i++) {
		$('#est'+i).hide();
	}
	$('#textdown').html("<a href='#'onclick='showHidden("+esti+")'><p> Mostrar Estados yá realizados</p><span class='glyphicon glyphicon-chevron-down'></span></a>");
}


// main info inicio hide- show
//
function lodPanel(id,ctrl)
{
    jVRI("#"+id).html("cargando...");
	jVRI("#"+id).load(ctrl);
}

function initProyPrec()
{
	// activamos el pre-revisor del PDF
	jVRI("#nomarch").change( function(e) {

		var grup = e.target.files;
		var file = grup[0];
		if( file.type!="application/pdf" || file.size>(2048000) ){
			jVRI("#nomarch").val("");
			jVRI("#filemsg").html( "No cumple con ser (PDF) de menos de 2MB");

			$("#nomarch").addClass("btn-danger");
			$("#nomarch").removeClass("btn-success");
		} else {
			$("#filemsg").html( "Archivo correcto (PDF) de menos de 2MB");
			$("#nomarch").addClass("btn-success");
			$("#nomarch").removeClass("btn-danger");
		}
	});
}

function lodShifs( id )
{
	if( id == 1 ) {
		$("#blq1").show();
		$("#blq2").hide();
	} else {
		$("#blq1").hide();
		$("#blq2").show();

		// activamos el pre-revisor del PDF
		initProyPrec();
	}
}

// enviar pdf correcs

function grabaCorrBorr()
{
    $("#plock").show();

    jVRI.ajax({
        url : "tesistas/execInCorrBorr",
        data : new FormData(frmborr),
        success : function( arg )
        {
            $("#plock").hide();
            $('#plops').html(arg);
        }
    });
}



// carga previa de borrador
//
function cargaBorr()
{
    jVRI.ajax({
        url : "tesistas/loadRegBorr",
        success : function( arg ){
            jVRI('#loadPy').html( arg );
            //---------------------------------------------------
            jVRI("#nomarch").change( function(e)
            {
                var grup = e.target.files;
                var file = grup[0];
                if( file.type!="application/pdf" || file.size>(5144600) ){
                    jVRI("#nomarch").val("");
                    jVRI("#filemsg").html( "No cumple con ser (PDF) de menos de 5MB");

                    $("#nomarch").addClass("btn-danger");
                    $("#nomarch").removeClass("btn-success");
                } else {
                    $("#filemsg").html( "Archivo correcto (PDF) de menos de 5MB");
                    $("#nomarch").addClass("btn-success");
                    $("#nomarch").removeClass("btn-danger");
                }
            });
            //---------------------------------------------------
        }
    });
}

// enviar pdf correcs
//unuv1.0 - estado revision 1
//unuv1.0 - estado revision 2
//unuv1.0 - estado revision 3
function grabaCorr()
{
    $("#plock").show();

    jVRI.ajax({
        url : "tesistas/execInCorr",
        data : new FormData(frmborr),
        success : function( arg )
        {
            $("#plock").hide();
            $('#plops').html(arg);
        }
    });
}

function grabaBorr()
{
    $("#plock").show();

    jVRI.ajax({
        url : "tesistas/execInBorr",
        data : new FormData(frmborr),
        success : function( arg )
        {
            $("#plock").hide();
            $('#plops').html(arg);
        }
    });
}

// cargar proyecto discriminado arg
// modificado unuv1 --(3.8.1)
// modificado unuv1 --(3.9.1)
function cargaProy(modo)
{
    var txt;
    if(modo==2)
    {
        setTimeout(document.getElementById('mos').style.display='block',5000);
        var codex = prompt("Ingrese el Código de su compañero", "");
        if (codex == null || codex == "") {
            if(codex == null)
            {
                txt='';
            }
            else
            {
                txt='Ingrese Codigo de su compañero'
            }

             document.getElementById("demo").innerHTML = txt;
        }        
        else
        {
            jVRI.ajax({
                url : "tesistas/ValidarCodigo/"+ codex, //(3.9.2)
                success : function( arg ){
                    if (!arg) 
                    {
                        jVRI.ajax({
                             url : "tesistas/loadRegProy/" + codex, //(3.9.3)
                            success : function( arg ){
                                jVRI('#loadPy').html( arg );
                                initProyPrec();
                            }
                        });                        
                    }
                    else
                    {                        
                        jVRI('#demo').html( arg );                         
                    }
                }
            });  
        }
    }
    else
    {
        jVRI.ajax({
            url : "tesistas/loadRegProy/" + codex,
            success : function( arg ){
                jVRI('#loadPy').html( arg );
                initProyPrec();
        }
    });
    }
    /*codex = (modo == 2)? prompt("Ingrese el Código de su compañero","") : "" ;

    jVRI.ajax({
        url : "tesistas/loadRegProy/" + codex,
        success : function( arg ){
            jVRI('#loadPy').html( arg );
			initProyPrec();
        }
    }); Modificado unuv1.0*/
}

//modificado unuv1 --(3.8.5)
//modificado unuv1 --(3.9.4)
function grabaProy()
{
    $("#plock").show();

    jVRI.ajax({
        url : "tesistas/execInProy",
        data : new FormData(frmproy),
        success : function( arg )
        {
            $("#plock").hide();
            $('#plops').html(arg);
        }
    });
}

function subBatch(){
    $("#plock").show();
    jVRI.ajax({
        url:"tesistas/execInBachi",
        data: new FormData(frmbach),
        success : function( arg ){
            $("#plock").hide();
            $("#plops").html(arg);
        }
    });
}


function solSusten(){
    $("#plock").show();
    jVRI.ajax({
        url:"tesistas/execSolSusten",
        data: new FormData(frmbach),
        success : function( arg ){
            $("#plock").hide();
            $("#plops").html(arg);
        }
    });
}

//modificado unuv1 --(3.8.3)
function cargaDocEnLin()
{
    jVRI( "#j4" ).load( "tesistas/loadLinCbo/4/" + cbolin.value );
}

///jVRI( "#j3" ).load( "tesistas/loadLinCbo/3/" + cbolin.value );
/*
function tesRevIgu()
{
    if( jVRI("#j4").val() == jVRI("#j3").val() )
        jVRI("#j3").val( 0 );
}
*/

// EOF