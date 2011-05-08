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
 		// Bucle para importar por fechas
 		$fecha_inicial = mktime(0,0,0,11,11,2008);
 		$fecha_final = time();// mktime(0,0,0,1,1,2009);
 		while( $fecha_inicial < $fecha_final ) {
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
 		if( $arrayopts == '' ) $arrayopts[ 'fin_max' ] = date('Y-m-d 23:59:59');
 		// TODO quitar esta linea, es solo debug y cambiar fin_max por fin
 		$arrayopts[ 'inicio_min' ]  = date('Y-m-01 00:00:00');
 		
 		// Obtenemos las incidencias
 		$incidencias = model_incident::get_incidents( $arrayopts );
 		
 		// JS googlemaps
 		$str.= '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>';	
 		
 		// Generamos el mapa
 		$str.= "<script type=\"text/javascript\">
 				function initialize(){
 					var latlng = new google.maps.LatLng(41.65,-0.87);
 					var myOptions = {
					    zoom: 12,
						center: latlng,
						mapTypeId: google.maps.MapTypeId.ROADMAP
 					};
 					var map = new google.maps.Map(document.getElementById('incidents_map'), myOptions);";
 		
 		if( is_array( $incidencias ) ) {
 			foreach( $incidencias as $incidencia ) {
 				$str.= "markerOpts = {flat:true,
 							map: map,
 							position: new google.maps.LatLng(" . $incidencia->get_latitud() . "," . $incidencia->get_longitud() . "),
 							title: '" . str_replace("\n",' ',$incidencia->get_titulo()) . "'};
						marker = new google.maps.Marker(markerOpts);";
 			}
 		}
 					
 		$str.= "}
 				$(document).ready(initialize);
 				</script>";

 		// Capas para el mapa
 		$str.= '<div id="incidents_map" style="height:' . $sizey . 'px;width:' . $sizex . 'px;"></div>';

 		echo $str;
 	}
 }
?>
