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
namespace ZGZagua\users;

/**
 * Modelo logico para las funcionalidades de los usuarios
 *
 * @author Victor Corbacho <victor@victorcorbacho.com>
 * @version 1.0.0 - 08/05/2011
 * @package users
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */
class model_user {

    public $id = -1; // ID del usuario
    public $email = '';
    public $password = '';
    public $registration_date = '';
    public $last_notified = '';
    public $active = false;
    public $active_key = '';

    /**
     * Constructor de la clase
     *
     * @param String $email Email del usuario
     * @param String $password Contraseña del usuario
     */
    function __construct( $email = '', $password = '', $externo = false ) {
        if ( $email ) {
            // Buscamos el usuario
            $database = new \ZGZagua\common\database();
            $results = $database->query( "SELECT * FROM users WHERE email='$email' AND active='1' LIMIT 1" );

            if ( $row = mysqli_fetch_array( $results ) ) {
                if ( $externo || $row[ 'password' ] === sha1( $password ) ) {
                    // Usuario valido
                    $this->set( $row );
                }
            }
        }
    }

    /**
     * Obtiene un usuario de la bdd a partir de su id
     *
     * @param int $id ID del usuario
     */
    function get( $id ) {
        // Buscamos el usuario
        $database = new \ZGZagua\common\database();
        $results = $database->query( "SELECT * FROM users WHERE id='$id' AND active='1' LIMIT 1" );

        if ( $row = mysqli_fetch_array( $results ) ) {
            // Usuario valido
            $this->set( $row );
        }
    }

    /**
     * Setea los atributos a partir de un array
     *
     * @param array $arrayopts Array de atributos
     */
    function set( $arrayopts ) {
        if ( isset( $arrayopts[ 'id' ] ) ) {
            $this->id = $arrayopts[ 'id' ];
        }
        if ( isset( $arrayopts[ 'registration_date' ] ) ) {
            $this->registration_date = $arrayopts[ 'registration_date' ];
        }
        if ( isset( $arrayopts[ 'email' ] ) ) {
            $this->email = $arrayopts[ 'email' ];
        }
        if ( isset( $arrayopts[ 'last_notified' ] ) ) {
            $this->last_notified = $arrayopts[ 'last_notified' ];
        }
        if ( isset( $arrayopts[ 'active' ] ) ) {
            $this->active = $arrayopts[ 'active' ];
        }
        if ( isset( $arrayopts[ 'active_key' ] ) ) {
            $this->active_key = $arrayopts[ 'active_key' ];
        }
        if ( isset( $arrayopts[ 'password' ] ) ) {
            $this->password = $arrayopts[ 'password' ];
        }
    }

    /**
     * Almacena el registro en la base de datos
     */
    public function store() {
        $sql = 'INSERT INTO users (';
        if ( $this->id != -1 && (string) $this->id !== '' ) {
            $sql .= 'id,';
        }
        $sql .= 'email,password,registration_date,last_notified,active,active_key) VALUES (';
        if ( $this->id != -1 && (string) $this->id !== '' ) {
            $sql .= "'" . $this->id . "',";
        }
        $sql .= "'" . $this->email . "','" . $this->password . "','" . $this->registration_date . "','" . $this->last_notified . "','" . $this->active . "','" . $this->active_key . "')";
        $database = new \ZGZagua\common\database();

        return $database->query( $sql );
    }

    /**
     * Actualiza un registro
     */
    public function update() {
        $sql = "UPDATE users SET active='" . $this->active . "' WHERE id='" . $this->id . "'";
        $database = new \ZGZagua\common\database();

        return $database->query( $sql );
    }
}
