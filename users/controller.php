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
 * Controlador para funcionalidades de usuarios
 *
 * @author Victor Corbacho <victor@victorcorbacho.com>
 * @version 1.0.0 - 08/05/2011
 * @package users
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */


namespace ZGZagua\users;

require_once( 'libs/openid.php' );

class controller {

    /**
     * Funcion para obtener login mediante openID
     */
    function login() {
        $provider = \Iternova\Common\Controller::get( 'provider' );

        // Proveedor
        switch ( $provider ) {
            case 'yahoo':
                $openid_url = 'http://me.yahoo.com';
                break;
            case 'google':
                $openid_url = 'https://www.google.com/accounts/o8/id';
                break;
            case 'facebook':
                $openid_url = 'facebook.anyopenid.com';
                break;
            case 'twitter':
                $openid_url = 'twitter.anyopenid.com';
                break;
        }

        if ( $provider === 'yahoo' || $provider === 'google' ) {
            // Usamos openID
            $openid = new LightOpenID;
            $openid->identity = $openid_url;
            $openid->returnUrl = 'http://zgzagua.es?action=login_confirm';
            $openid->required = [ 'namePerson/friendly', 'contact/email', 'namePerson', 'identityProvider/userId' ];
            echo '<script type="text/javascript">$(location).attr(\'href\',\'' . $openid->authUrl() . '\');</script>';
        } elseif ( $provider === 'twitter' ) {
            // Usamos twitter oauth
            /* Start session and load library. */
            require_once( 'libs/twitter/twitteroauth/twitteroauth.php' );
            require_once( 'libs/twitter/config.php' );

            /* Build TwitterOAuth object with client credentials. */
            $connection = new TwitterOAuth( CONSUMER_KEY, CONSUMER_SECRET );

            /* Get temporary credentials. */
            $request_token = $connection->getRequestToken( OAUTH_CALLBACK );

            /* Save temporary credentials to session. */
            $_SESSION[ 'oauth_token' ] = $token = $request_token[ 'oauth_token' ];
            $_SESSION[ 'oauth_token_secret' ] = $request_token[ 'oauth_token_secret' ];
            echo '<script type="text/javascript">$(location).attr(\'href\',\'' . $connection->getAuthorizeURL( $token ) . '\');</script>';
        }
    }

    function login_verify() {
        if ( \Iternova\Common\Controller::get( 'provider' ) === 'zgzagua' ) {
            // Login normal
            self::user_login();
        } elseif ( \Iternova\Common\Controller::get( 'oauth_token' ) !== '' ) {
            //Twitter
            require_once( 'libs/twitter/twitteroauth/twitteroauth.php' );
            require_once( 'libs/twitter/config.php' );

            /* If the oauth_token is old redirect to the connect page. */
            if ( isset( $_REQUEST[ 'oauth_token' ] ) && $_SESSION[ 'oauth_token' ] !== $_REQUEST[ 'oauth_token' ] ) {
                $_SESSION[ 'oauth_status' ] = 'oldtoken';
                header( 'Location: index.php' );
            }

            /* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
            $connection = new TwitterOAuth( CONSUMER_KEY, CONSUMER_SECRET, $_SESSION[ 'oauth_token' ], $_SESSION[ 'oauth_token_secret' ] );

            /* Request access tokens from twitter */
            $access_token = $connection->getAccessToken( $_REQUEST[ 'oauth_verifier' ] );

            /* Save the access tokens. Normally these would be saved in a database for future use. */
            $_SESSION[ 'access_token' ] = $access_token;

            /* Remove no longer needed request tokens */
            unset( $_SESSION[ 'oauth_token' ], $_SESSION[ 'oauth_token_secret' ] );

            /* If HTTP response is 200 continue otherwise send to connect page to retry */
            if ( 200 === (int) $connection->http_code ) {
                /* The user has been verified and the access tokens can be saved for future use */
                $_SESSION[ 'status' ] = 'verified';
            }
            header( 'Location: ./index.php' );

        } elseif ( \Iternova\Common\Controller::get( 'openid_mode' ) !== '' ) {
            // OpenID
            $openid = new LightOpenID;
            $openid->data = $_GET;
            $attributes = $openid->getAttributes();
            self::user_login( $attributes[ 'contact/email' ], true );
        }
    }

    /**
     * Funcion para hacer efectivo el login
     *
     * @param String $email Email del usuario
     * @param boolean $external Indica si se ha autenticado con otro servicio (true)
     * @param String $password Contraseña, si external=false
     */
    private function user_login( $email = '', $external = false, $password = '' ) {
        if ( $email === '' ) {
            $email = \Iternova\Common\Controller::post( 'e-mail' );
        }
        if ( $password === '' ) {
            $password = \Iternova\Common\Controller::post( 'password' );
        }

        // Si external es true miramos si esta en la bdd
        $usuario = $external ? new model_user( $email, '', true ) : new model_user( $email, $password );

        // Comprobamos si esta bien
        if ( $usuario->registration_date !== '' ) {
            // Esta registrado
            $_SESSION[ 'userID' ] = $usuario->id;
            self::user_profile();
        } else {
            // Se tiene que registrar
            self::user_register( $email, $external );
        }
    }

    /**
     * Funcion para registrar un usuario
     *
     * @param String $mail Email del usuario
     */
    public function user_register( $mail = '', $external = false ) {
        if ( $mail === '' ) {
            $mail = \Iternova\Common\Controller::post( 'e-mail' );
        }

        // Mostramos pantalla para registro
        echo '<div style="margin-left:30px;margin-bottom:30px;"><h1>Registro de usuario</h1>';
        if ( $external || ( \Iternova\Common\Controller::post( 'register' ) &&
                ( preg_match( "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", \Iternova\Common\Controller::post( 'e-mail' ) ) && \Iternova\Common\Controller::post( 'password' ) != '' && strlen( \Iternova\Common\Controller::post( 'password' ) ) > 5 && \Iternova\Common\Controller::post( 'password' ) == \Iternova\Common\Controller::post( 'repassword' ) ) ) ) {
            // Alamacenamos el usuario
            // Mensaje para que active el usuario
            $active_key = sha1( time() . rand() );
            $usuario = new model_user();
            $usr_opts[ 'email' ] = mysqli_escape_string( \Iternova\Common\Controller::post( 'e-mail' ) );
            $usr_opts[ 'password' ] = sha1( \Iternova\Common\Controller::post( 'password' ) );
            $usr_opts[ 'registration_date' ] = date( 'Y-m-d H:i:s' );
            $usr_opts[ 'active_key' ] = $active_key;
            $usuario->set( $usr_opts );
            if ( $usuario->store() ) {
                $url = 'http://zgzagua.es?action=activateuser&key=' . $active_key . '&email=' . $usuario->email;
                $link = '<a href="' . $url . '">' . $url . '</a>';
                $para = \Iternova\Common\Controller::post( 'e-mail' );
                $titulo = 'Registro en ZGZagua';
                $mensaje = \Iternova\Common\Controller::show_html_header( false, false ) . '<body><div id="container"><div id="header"></div>Gracias por registrarse en <a href="http://zgzagua.es">ZGZagua</a>. Para activar su cuenta y configurar sus notificaciones, por favor, pulse el siguiente enlace: ' . $link . '</div></body></html>';

                $cabeceras = 'MIME-Version: 1.0' . "\r\n";
                $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

                // Cabeceras adicionales
                $cabeceras .= 'From: ZGZagua <admin@zgzagua.es>' . "\r\n";

                mail( $para, $titulo, $mensaje, $cabeceras );

                echo '<h3>Por favor, compruebe su correo para activar su cuenta.</h3>';
            } else {
                echo '<h3>Ocurri&oacute; un error al registrar su usario. ¿Est&aacute; ya registrado?</h3>';
            }
        } else {
            if ( \Iternova\Common\Controller::post( 'register' ) ) {
                // Ha habido un error
                echo '<div style="background:red;padding-left:5px;"><h2>Por favor, compruebe que ha escrito una direcci&oacute;n de correo v&aacute;lida y una contrase&ntilde;a de al menos seis caracteres.</h2></div>';
            }
            echo '<form name="register" action="index.php?action=register" method="post">';
            echo '<table><tr><td><b>E-mail:</b></td><td><input type="text" name="e-mail" value="' . $mail . '"/></td></tr>';
            echo '<tr><td><b>Contrase&ntilde;a:</b></td><td><input type="password" name="password" /></td></tr>';
            echo '<tr><td><b>Repetir contrase&ntilde;a:</b></td><td><input type="password" name="repassword" /></td></tr></table>';
            echo '<input type="submit" name="register" value="Aceptar"/>';
            echo '</form>';
        }
        echo '</div>';
    }

    /**
     * Activa un usuario
     */
    public function user_activate() {
        $key = \Iternova\Common\Controller::get( 'key' );
        $email = \Iternova\Common\Controller::get( 'email' );
        // Buscamos el usuario
        $database = new database();
        $sql = "SELECT * FROM users WHERE email='$email' AND active='0' AND active_key='$key' LIMIT 1";
        $results = $database->query( $sql );

        if ( $row = mysqli_fetch_array( $results ) ) {
            $usuario = new model_user();
            $usuario->set( $row );
            $usuario->active = true;
            $usuario->update();
            // Usuario activado, vamos al perfil
            echo '<div style="didth:100%;text-align:center"><h2>Redirigiendo a su perfil...</h2><img src="img/loading.gif" alt="" />';

            self::user_login( $usuario->email, true );
        } else {
            // Error
            echo '<div style="padding-left:30px;padding-bottom:30px;"><h1>Activar usuario</h1><h2>Ha ocurrido un error, el enlace no es v&aacute;lido o su usario ya est&aacute; activo.</h2></div>';
        }
    }

    /**
     * Cerrar la sesion
     */
    public function logout() {
        session_destroy();
        echo '<div style="padding:10px 30px 30px 30px;text-align:center;">Su sesi&oacute;n ha finalizado, ¡hasta pronto!</div>';

    }

    /**
     * Perfil del usuario
     */
    public function user_profile() {
        if ( isset( $_SESSION[ 'userID' ] ) ) {
            $usuario = new model_user();
            $usuario->get( $_SESSION[ 'userID' ] );
            echo '<div style="padding:10px 30px 30px 30px;">';
            echo '<h1>Perfil personal</h1>';
            echo '<span class="b">E-mail: </span>' . $usuario->email;
            echo '<h2>Alertas registradas <a href="index.php?action=nueva_alerta">[Registrar nueva alerta]</a></h2>';
            \ZGZagua\incidents\controller::display_incidents( $usuario->id );
            echo '</div>';
        } else {
            echo 'No autorizado.';
        }
    }

    /**
     * Obtener numero de usuarios
     */
    public static function user_count() {
        $ret = 0;
        $database = new \ZGZagua\common\database();

        $sql = "SELECT count(id) contador FROM users WHERE 1";

        $resultado = $database->query( $sql );

        if ( $row = mysqli_fetch_array( $resultado ) ) {
            $ret = $row[ 'contador' ];
        }

        return max( $ret, 100 );
    }

}
