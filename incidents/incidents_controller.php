<?php
/*
 * ZGZagua
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 /**
  * Controlador para la funcionalidad de incidencias
  * 
 * @author Victor Corbacho <victor@victorcorbacho.com>
 * @version 1.0.0 - 07/05/2011
 * @package incidents
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */
 
 require_once( 'incidents_model.php' );
 require_once( 'incidents_xml_model.php' );
 
 class controller_incidents {
 	/**
 	 * Carga datos del rss del ayuntamiento
 	 */
 	function load_xml() {
 		echo "Importando datos...<br/>";
 		// Bucle para importar por fechas, vamos a importar siempre desde el dia anterior hasta el posterior por si acaso
 		$fecha = time;
 		$fecha_inicial = ($fecha - 86400);
 		$fecha_final = ($fecha + 86400);
 		while( $fecha_inicial <= $fecha_final ) {
 			$fecha = date('d/m/Y', $fecha_inicial);
 			new model_incident_xml( $fecha, $fecha );
 			$fecha_inicial+= 86400;
 		}
 		echo "Datos importados";
 		
 	}
 	/**
 	 * Genera un mapa con las incidencias obtenidas a partir de los parametros pasados
 	 * @param Array $arrayops Array de opciones para la busqueda de cortes de agua.
 	 * @param int $sizex Ancho del mapa
 	 * @param int $sizey Alto del mapa
 	 */
 	function create_map( $arrayopts = '', $sizex = 600, $sizey = 400 ) {
 		$str = '';
 		// Si no se pasa fecha, por defecto se sacan las de hoy
 		if( $arrayopts == '' ) $arrayopts[ 'fin_min' ] = date('Y-m-d 00:00:00');
 		if( $arrayopts == '' ) $arrayopts[ 'fin_max' ] = date('Y-m-d 23:59:59');
 		
 		// Obtenemos las incidencias
 		$incidencias = model_incident::get_incidents( $arrayopts );
 		
 		// JS googlemaps
 		$str.= '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>';	
 		$rand = rand();
 		// Generamos el mapa
 		$str.= "<script type=\"text/javascript\">
 				function initialize(){
 					var latlng = new google.maps.LatLng(41.65,-0.87);
 					var myOptions = {
					    zoom: 12,
						center: latlng,
						mapTypeId: google.maps.MapTypeId.ROADMAP,
						streetViewControl: false
 					};
 					var infowindow = new google.maps.InfoWindow();
 					var map$rand = new google.maps.Map(document.getElementById('incidents_map$rand'), myOptions);
 					google.maps.event.addListener(map$rand, 'click', function() {
						    infowindow.close();
					});
 					function createMarker(latitud,longitud,titulo,descripcion,id){
 						var position = new google.maps.LatLng(latitud,longitud);
 						markerOpts = {flat:true,map:map$rand,position:position,title:titulo };
 						id.setOptions(markerOpts);
 						google.maps.event.addListener(id, 'click', function() {
						    map$rand.setCenter(position);
						    infowindow.setContent(descripcion);
						    infowindow.setPosition(position);
						    infowindow.open(map$rand);
						 });
 					}";
 		
 		if( is_array( $incidencias ) ) {
 			foreach( $incidencias as $incidencia ) {
 				$str.= "marker" . $incidencia->get_id() . "=new google.maps.Marker;createMarker(" . $incidencia->get_latitud() . "," . $incidencia->get_longitud() . ",'" . $incidencia->get_titulo() . "','<div class=\"map_description\">" . $incidencia->get_descripcion() . "</div>',marker" . $incidencia->get_id() . ");";
 			}
 		}
 					
 		$str.= "}
 				$(document).ready(initialize);
 				</script>";

 		// Capas para el mapa
 		$str.= '<div class="incidents_map" id="incidents_map' . $rand . '" style="height:' . $sizey . 'px;width:' . $sizex . 'px;"></div>';

 		echo $str;
 	}
 	
 	/**
 	 * Muestra una lista de los cortes obtenidos a partir de las opciones de busqueda
 	 */
 	 function generate_list( $arrayopts = '' ) {
 	 	$str = '';
 	 	
 	 	// Si no hay opciones, mostraremos los cortes para hoy
 		if( $arrayopts == '' ) $arrayopts[ 'fin_min' ] = date('Y-m-d 00:00:00');
 		if( $arrayopts == '' ) $arrayopts[ 'fin_max' ] = date('Y-m-d 23:59:59');
 		
 		$incidencias = model_incident::get_incidents( $arrayopts );
 		
 		if( is_array( $incidencias ) && sizeof( $incidencias) > 0 ) {
 			// Generamos la lista
 			$str.= '<div style="height:320px; overflow:auto;"><table id="list_current_cortes">';
 			
 			foreach( $incidencias as $incidencia ) {
 				$id = $incidencia->get_id();
 				$staticmap = 'http://maps.google.com/maps/api/staticmap?zoom=16&size=115x115&maptype=roadmap&markers=' . $incidencia->get_latitud() . ',' . $incidencia->get_longitud() . '&sensor=false';
 				$str.= '<tr><td><a id="' . $id . '" href="#' . $id . '" onclick="google.maps.event.trigger(marker' . $id . ',\'click\');"><img src="' . $staticmap . '" alt=""/></a></td>';
				$str.= '<td><h2>' . $incidencia->get_titulo() . '</h2>' . $incidencia->get_observaciones() . '</td></tr>';
 			}
 			
 			$str.= '</table></div>';
 		} else {
 			// Mostramos mensaje de que no hay incidencias
 			$str.= '<h2><img src="img/ok.png" alt=""/>No hay ning&uacute;n corte previsto.</h1>';
 		}
 		
 		echo $str;
 	 }
 	 
 	 /**
 	  * Muestra una lista con las alertas de un usuario
 	  * @param int $id ID del usuario
 	  */
 	  public function display_incidents($id){
 	  	$database = new database();
 	  	$sql = "SELECT * FROM locations WHERE user_id='$id'";
 	  	$resultados = $database->query($sql);

 	  	if ($row = mysql_fetch_array($resultados) ) {
 			// Generamos la lista
 			$str.= '<div style="height:320px; overflow:auto;"><table id="list_current_cortes">';
 			
 			do {
 				$staticmap = 'http://maps.google.com/maps/api/staticmap?zoom=16&size=115x115&maptype=roadmap&markers=' . $row['latitud'] . ',' . $row['longitud'] . '&sensor=false';
 				$str.= '<tr><td><img src="' . $staticmap . '" alt=""/></td>';
				$str.= '<td><h2>' . $row['direccion'] . ', ' . $row['numero'] . '</h2></td></tr>';
 			}while($row = mysql_fetch_array($resultados) );
 			
 			$str.= '</table></div>';
 			echo $str;
 	  	} else {
 	  		echo 'No tiene alertas registradas.';
 	  	}
 	  }
 	  
 	  /**
 	   * Formulario para nueva alerta
 	   */
 	   public function add_alert(){
 	   		if( isset( $_SESSION[ 'userID' ] ) && $_SESSION[ 'userID' ] != '' ) {
 	   			if(Controller::post('alerta' ) != '' ) {
 	   				$latitud = mysql_escape_string(Controller::post('latitud'));
 	   				$longitud = mysql_escape_string(Controller::post('longitud'));
 	   				$direccion = mysql_escape_string(Controller::post('direccion'));
 	   				$direccion = explode(',',$direccion);
 	   				$numero = '';
 	   				if(sizeof($direccion) >= 2) {
 	   					if(is_numeric($direccion[1]))$numero = $direccion[1];
 	   					$direccion = reset($direccion);
 	   				}
 	   				if(is_array($direccion)) $direccion = reset($direccion);
 	   				$sql = "INSERT INTO locations (direccion,numero,latitud,longitud,user_id) VALUES ('$direccion','$numero','$latitud','$longitud','" . $_SESSION[ 'userID' ] . "')";

 	   				$database = new database();
 	   				$database->query($sql);
 	   				echo '<h2 style="padding-left:30px;">Alerta guardada correctamente. Ir al <a href="index.php?action=perfil">perfil</a>.</h2>';
 	   			} else {
 	   				// Formulario para registrar alerta
 	   				echo '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>	
<script type="text/javascript">
 $(document).ready(function(){

  var mapOptions = {
       zoom: 10,
       mapTypeId: google.maps.MapTypeId.ROADMAP,
       center: new google.maps.LatLng(41.65,-0.87)
     };

  var map = new google.maps.Map(document.getElementById("map"),mapOptions);
  var marker = new google.maps.Marker();
  var geocoder = new google.maps.Geocoder();  

     $(function() {
         $("#direccion").autocomplete({
          
           source: function(request, response) {

          if (geocoder == null){
           geocoder = new google.maps.Geocoder();
          } 
             geocoder.geocode( {\'address\': request.term +\',Zaragoza\' }, function(results, status) {
               if (status == google.maps.GeocoderStatus.OK) {

                  var searchLoc = results[0].geometry.location;
               	  var lat = results[0].geometry.location.lat();
                  var lng = results[0].geometry.location.lng();
                  var latlng = new google.maps.LatLng(lat, lng);
                  var bounds = results[0].geometry.bounds;
                  marker.setOptions({map:map,position:latlng});
                  $("#latitud").val(lat);
				  $("#longitud").val(lng);
                  geocoder.geocode({\'latLng\': latlng}, function(results1, status1) {
                      if (status1 == google.maps.GeocoderStatus.OK) {
                        if (results1[1]) {
                         response($.map(results1, function(loc) {
                         return {
                            label  : loc.formatted_address,
                            value  : loc.formatted_address,
                            bounds   : loc.geometry.bounds
                          }
                        }));
                        }
                      }
                    });
            }
              });
           },
           select: function(event,ui){
      var pos = ui.item.position;
      var lct = ui.item.locType;
      var bounds = ui.item.bounds;

      if (bounds){
       map.fitBounds(bounds);
      }
           }
         });
     });   
 });
</script>
';
 	   				echo '<div style="padding:20px 30px 30px 30px;"><h1>Registrar nueva alerta</h1>';
 	   				echo '<form name="alerta" action="index.php?action=nueva_alerta" method="post">';
 	   				echo '<input type="hidden" id="latitud" name="latitud"/>';
 	   				echo '<input type="hidden" id="longitud" name="longitud"/>';
 	   				echo '<span class="b">Direcci&oacute;n (v&iacute;a, n&uacute;mero):</span> <input type="text" id="direccion" name="direccion"/><br/>';
 	   				echo '<input type="submit" name="alerta" value="Aceptar"/>';
 	   				echo '</form><div id="map" style="margin-top:-85px;margin-left:425px;height:400px;width:400px;"></div>';
 	   				echo '</div>';
 	   			}
 	   		} else {
 	   			echo '<h2 style="padding-left:30px;">Debe registrarse para acceder a esta funcionalidad.</h2>';
 	   		}
 	   }
 	   
 	   /**
 	    * Lista de sugerencias
 	    */
 	   public function suggest() {
	 	   $return_arr = array();
	 	   $database = new database();
	 	   $sql = "SELECT direccion FROM cortes where direccion like '%" . mysql_real_escape_string($_GET['term']) . "%'"; 
	 	   $resultado = $database->query($sql);
	 	   /* Retrieve and store in array the results of the query.*/
	 	   
	 	   while ($row = mysql_fetch_array($resultado)) {
		 	   $row_array['direccion'] = $row['direccion'];
		 	   
		 	   array_push($return_arr,$row_array);
		 	   
	 	   }
	 	   
	 	   /* Toss back results as json encoded array. */
	 	   echo json_encode($return_arr);
	 	   
 	   }
 	   
 	   /**
 	    * Funcion para estadisticas
 	    */
 	    function reports(){
 	    	$periodo = Controller::get('periodo');
 	    	echo '<div id="tabs" style="width:960px; margin:0 auto; padding:20px 0 30px 0;">';
 	    	switch($periodo) {
 	    		case 'mes':
 	    			echo '<h1>Incidencias del &uacute;ltimo mes</h1><a href="index.php?action=estadisticas"><h3>[&Uacute;ltima semana]</h3></a>';
					$fin = time();
					$inicio = $fin - (24*60*60*30);
					
					$fin = date('Y-m-d 23:59:59',$fin);
					$inicio = date('Y-m-d 00:00:00',$inicio);
					echo '<div style="width:100%; margin:auto;">';
					self::create_map(array('inicio_min'=>$inicio, 'fin_max'=>$fin));
					echo '</div>';
 	    			break;
 	    		default:
 	    			echo '<h1>Incidencias de la &uacute;ltima semana</h1><a href="index.php?action=estadisticas&periodo=mes"><h3>[&Uacute;ltimo mes]</h3></a>';
					$fin = time();
					$inicio = $fin - (24*60*60*7);
					
					$fin = date('Y-m-d 23:59:59',$fin);
					$inicio = date('Y-m-d 00:00:00',$inicio);
					
					echo '<div style="width:100%; margin:auto;">';
					self::create_map(array('inicio_min'=>$inicio, 'fin_max'=>$fin));
					echo '</div>';
 	    	}
			
			echo '</div>';
 	    }
 	    
 	    /**
 	     * Notifica a los usuarios los cortes de agua en su calle
 	     */
 	    function notify() {
 	    	// Cogemos todos los usuarios a los que no hemos notificado hoy
 	    	$database = new database();
 	    	$fecha = date('Y-m-d 23:59:59');
 	    	$sql = "select direccion,numero,email from locations l left join users u on u.id=l.user_id where u.last_notified<'$fecha'";
 	    	
 	    	$usr_resultado = $database->query($sql);
 	    	
 	    	if($usuario = mysql_fetch_array($usr_resultado)){
 	    		// Esto es muy poco eficiente, pero no da tiempo a optimizarlo, como en principio
 	    		// no se van a hacer muchas consultas tiene paso, pero cuando termine el plazo de 
 	    		// votaciones hay que cambiarlo
 	    		do{
 	    			$notificaciones = '';
 	    			$direccion = str_replace(' ','%',$usuario['direccion']);
	 	    		$sql = "SELECT * FROM cortes WHERE fin=$fecha AND direccion LIKE '$direccion' order by fin DESC limit 1"; // Buscamos el ultimo para cada direccion
	 	    		$cortes = $database->query($sql);
	 	    		if( $calle = mysql_fetch_array($cortes)){
	 	    			// Comprobamos numeros
	 	    			$notificar = false;
	 	    			if($usuario['numero'] != ''){
	 	    				if($calle['par'] || $calle['impar']) {
	 	    					if($calle['par'] && (($usuario['numero']%2) == 0)){
	 	    						$notificar = true;
	 	    					} elseif( $usuario['impar'] ) {
	 	    						$notificar = true;
	 	    					}
	 	    				} else {
	 	    					$notificar = true;
	 	    				}
	 	    			} else {
	 	    				$notificar = true;
	 	    			}
	 	    			if ($notificar) {
	 	    				$notificaciones.= '<li>' . $calle['titulo'] . ': ' . $calle['observaciones'] . '</li>';
	 	    			}
	 	    		}
 	    	
		 	    	if($notificaciones!='') {
		 	    		$notificaciones = '<ul>' . $notificaciones . '</ul>';
		 	  			$para   = Controller::post('e-mail');
						$titulo = 'Registro en ZGZagua';
						$mensaje = Controller::show_html_header(false,false) . '<body><div id="container"><div id="header"></div>Se han detectado las siguientes coincidencias entre los cortes programados por el Ayuntamiento de Zaragoza y sus alertas: ' . $notificaciones . '<br/><br/><a href="http://zgzagua.es">ZGZagua</a></div></body></html>';
						
						$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
						$cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
						
						// Cabeceras adicionales
						$cabeceras .= 'From: ZGZagua <avisos@zgzagua.es>' . "\r\n";
						
						mail($para, $titulo, $mensaje, $cabeceras);
		 	    	}
 	    		}while($usuario = mysql_fetch_array($usr_resultado));
 	    	}
 	    }
 }
?>
