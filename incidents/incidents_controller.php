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
 }
?>
