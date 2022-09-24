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
	 * @param boolean $echo Lo muestra por pantalla si true
	 * @param boolean $script Incluye scripts
	 */
	public static function show_html_header( $echo = true, $script = true ) {
		$str = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>ZGZagua</title>
		<link rel="stylesheet" type="text/css" href="css/default.css" />
		<link rel="stylesheet" type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" />
		<link rel="shortcut icon" href="img/favicon.ico">';
		if($script) $str.= '<script type="text/javascript" src="https://www.google.com/jsapi?key=' . self::google_key() . '"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script><script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js"></script>';
		$str.= '</head>';
		if($echo) echo $str; else return $str;
	}

	/**
	 * Funcion para mostrar el pie html
	 */
	public static function show_html_footer($echo = true) {
		if( $echo) echo '</html>'; else return '</html>';;
	}

	/**
	 * Funcion para mostrar el cuerpo de la pagina
	 */
	public static function show_html_body() {
		echo '<body><div id="container2">';
		// Div cabecera
		echo '<div id="header">';
		// Enlace login
		/*$url_login = 'index.php?action=login&provider=';
		echo '<div id="login_btn" onclick="$(\'#login_box\').toggle(\'normal\');"></div>';
		echo '<div id="login_box">';
		if (isset($_SESSION[ 'userID' ] ) && $_SESSION[ 'userID' ] != '' ) {
			// Usuario logeado
			echo 'Se encuentra registrado.<ul><li><a href="index.php?action=perfil">Ver perfil</a></li><li><a href="index.php?action=logout">Salir</a></li></ul>';
		} else {
			// Formulario de login
			echo '<form id="login" name="login" action="index.php?action=login_confirm&provider=zgzagua" method="post"><table><tr><td>E-mail:</td><td><input type="text" name="e-mail"/></td></tr><tr><td>Contrase&ntilde;a:</td><td><input type="password" name="password"/></td></tr></table><input type="submit" name="login" value="Aceptar"/></form>';
			echo '<br/>Conectar con...<br/><table class="fullwidth"><tr>' .
					//'<td><a href="#" onclick="$(\'#login_box\').load(\'' . $url_login . 'facebook\');return false;"><img src="img/btn_fb.png" alt=""/></a></td>' .
					'<td><a href="#" onclick="$(\'#login_box\').load(\'' . $url_login . 'yahoo\');return false;"><img src="img/btn_yh.png" alt=""/></a></td>' .
					'<td><a href="#" onclick="$(\'#login_box\').load(\'' . $url_login . 'google\');return false;"><img src="img/btn_g.png" alt=""/></a></td>' .
					//'<td><a href="#" onclick="$(\'#login_box\').load(\'' . $url_login . 'twitter\');return false;"><img src="img/btn_twitter.png" alt=""/></a></td>' .
					'</tr></table>';
		}
		echo '</div>';*/
		// Enlaces menu superior
		echo '<div id="menu_home"><a href="index.php">HOME</a></div>';
		echo '<div id="menu_estadisticas"><a href="index.php?action=estadisticas">ESTAD&Iacute;STICAS</a></div>';
		echo '<div id="menu_cortes"><a href="index.php?action=cortes">CORTES PREVISTOS</a></div>';

		echo '</div>';

		// Div cuerpo
		echo '<div id="container">';
		$accion = Controller::get('action');

		if ( $accion == 'login' ) {
			// Login de usuario (parte 1)
			controller_user::login();
		} elseif ($accion == 'login_confirm' ) {
			// Login de usuario (confirmacion openID)
			controller_user::login_verify();
		} elseif( $accion == 'register' ) {
			controller_user::user_register();
		} elseif( $accion == 'activateuser' ) {
			controller_user::user_activate();
		} elseif( $accion == 'logout' ) {
			controller_user::logout();
		} elseif( $accion == 'perfil' ) {
			controller_user::user_profile();
		} elseif( $accion == 'nueva_alerta' ) {
			controller_incidents::add_alert();
		} elseif( $accion == 'cortes' ) {
			// Cortes pervistos para hoy
			$arrayopts[ 'fin_min' ] = date('Y-m-d 00:00:00');
			$arrayopts[ 'fin_max' ] = date('Y-m-d 23:59:59');
			// Mostramos los datos
			echo '<table id="container_cortes"><tr><td>
			<h1>Cortes previstos para el ' . strftime("%e de %B de %Y",time()) . '</h1>';

			controller_incidents::generate_list( $arrayopts );

			echo '</td><td style="padding:50px 20px 20px 10px;">';

			controller_incidents::create_map( $arrayopts, 500, 400 );

			echo '</td></tr></table>';
		} elseif( $accion == 'estadisticas' ){
			controller_incidents::reports();
		} else {
			// Portada
			echo '<div id="container_index"><div id="map_index">';
			controller_incidents::create_map();
			echo '</div><div id="user_counter">' . controller_user::user_count() . '</div><div id="register_btn" onclick="$(location).attr(\'href\',\'index.php?action=register\')"></div></div>';
		}
		echo '</div>';

		// Pie
		echo '<div id="footer">ZGZagua participa en el desaf&iacute;o <a href="http://live.abredatos.es/teams/22">AbreDatos 2011</a>. ZGZAgua ' . date('Y') . ' </div>';

		echo '</div></body>';
	}

	/**
	 * Funcion para obtener datos de $_GET
	 * @param String $key Clave que queremos obtener
	 */
	 public static function get( $key ) {
	 	$return = '';
	 	if ( isset( $_GET[$key] ) ) {
	 		$return = trim( $_GET[$key] );
	 	}
	 	return $return;
	 }

	 /**
	  * Funcion para obtener datos de $_POST
	  */
	 public function post( $key ) {
	 	$return = '';
	 	if ( isset( $_POST[$key] ) ) {
	 		$return = trim( $_POST[$key] );
	 	}
	 	return $return;
	 }

	 /**
	  * Proporciona la api key de google asociada al dominio
	  */
	 public static function google_key() {
	 	return 'ABQIAAAA2JikbGJZ0fUtFRmFto1WoBT2yXp_ZAY8_ufC3CFXhHIE1NvwkxTDj_fgsM8tRX5c1uxioRFlfhBb0Q';// Local
//	 	return 'ABQIAAAA2JikbGJZ0fUtFRmFto1WoBSbRSau-Xwlj9CD1S8yfl-npsugQxQVkPFlEvHEqjKZlq2PBHAQtPgaEA';// remoto
	 }
}
?>
