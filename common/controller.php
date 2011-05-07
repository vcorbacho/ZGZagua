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
  * Controlador de la aplicacion
  * 
 * @author Victor Corbacho <victor@victorcorbacho.com>
 * @version 1.0.0 - 07/05/2011
 * @package common
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */
 
class Controller {
	/**
	 * Funcion para mostrar la cabecera html
	 */
	public function show_html_header() {
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es">
		<head>
		</head>';
	}
	
	/**
	 * Funcion para mostrar el pie html
	 */
	public function show_html_footer() {
		echo '</html>';
	}
	
	/**
	 * Funcion para mostrar el cuerpo de la pagina
	 */
	public function show_html_body() {
		echo '<body>';
		echo '</body>';
	}
	
	/**
	 * Funcion para obtener datos de $_GET
	 * @param String $key Clave que queremos obtener
	 */
	 public function get( $key ) {
	 	$return = '';
	 	if ( isset( $_GET[$key] ) ) {
	 		$return = trim( $_GET[$key] );
	 	}
	 	return $return;
	 }
}
?>
