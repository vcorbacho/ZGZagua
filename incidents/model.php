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
 * Modelo logico para la funcionalidad de incidencias
 *
 * @author Victor Corbacho <victor@victorcorbacho.com>
 * @version 1.0.0 - 07/05/2011
 * @package
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */
namespace ZGZagua\incidents;

class model {

    protected $id = -1;
    protected $titulo = '';
    protected $direccion = '';
    protected $inicio = '';
    protected $fin = '';
    protected $motivo = '';
    protected $tramo = '';
    protected $observaciones = '';
    protected $latitud = '';
    protected $longitud = '';

    protected $pares = false;
    protected $impares = false;

    /**
     * Constructor de la clase
     *
     * @param Object $incidencia Objeto de tipo SimpleXMLElement
     */
    function __construct( $incidencia = '' ) {
        if ( is_a( $incidencia, 'SimpleXMLElement' ) ) {
            // Sacamos la descripcion
            $descripcion = $incidencia->description;
            $descripcion = $descripcion->children();
            $descripcion = reset( $descripcion );

            // Ponemos los atributos
            $this->titulo = $this->sanitize( $incidencia->title );
            $this->direccion = $this->sanitize( $descripcion[ 0 ] );
            $this->inicio = $this->sanitize( $descripcion[ 1 ] );
            $this->fin = $this->sanitize( $descripcion[ 2 ] );
            $this->motivo = $this->sanitize( $descripcion[ 3 ] );
            $this->tramo = $this->sanitize( $descripcion[ 4 ] );
            $this->observaciones = $this->sanitize( $descripcion[ 6 ] );

            // Coordenadas
            $punto = reset( $incidencia->children( 'http://www.georss.org/georss' ) );
            $punto = explode( ' ', $punto );
            $this->latitud = $punto[ 0 ];
            $this->longitud = $punto[ 1 ];

            // Intervalo de horas
            preg_match_all( '/\d{1,2}/', $this->observaciones, $horas );
            $horas = reset( $horas );
            if ( sizeof( $horas == 2 ) ) {
                // Se han encontrado dos horas
                $desde = reset( $horas );
                $hasta = end( $horas );
                // Ponemos ceros delante si procede
                if ( strlen( $desde ) == 1 ) {
                    $desde = "0$desde";
                }
                if ( strlen( $hasta ) == 1 ) {
                    $hasta = "$hasta";
                }
                // Las agregamos a inicio y fin
                $this->inicio .= " $desde:00";
                $this->fin .= " $hasta:00";
            } else {
                // Como no hay horas, vamos a poner de las 00.00 a las 23.59
                $this->inicio .= " 00:00";
                $this->fin .= " 23:59";
            }

            // ID
            $link = $incidencia->link;
            $link = explode( 'incidencia=', $link );
            if ( isset( $link[ 1 ] ) && is_numeric( $link[ 1 ] ) ) {
                $this->id = $link[ 1 ];
            }

            // Pares o impares
            preg_match( '/ pares/', $this->titulo, $pares );
            if ( !empty( $pares ) ) {
                $this->pares = true;
            }
            preg_match( '/ impares/', $this->titulo, $impares );
            if ( !empty( $impares ) ) {
                $this->impares = true;
            }
        } elseif ( is_array( $incidencia ) ) {
            $this->set( $incidencia );
        }
    }

    /**
     * Obtener registros almacenados a partir de un filtro.
     *
     * @param array $search_opts Array con opciones de busqueda
     *
     * @return array
     */
    public static function get_incidents( $search_opts ) {
        $database = new \ZGZagua\common\database();
        $array_return = [];

        $where = ' WHERE 1 ';

        if ( is_array( $search_opts ) ) {
            foreach ( $search_opts as $opt => $value ) {
                switch ( $opt ) {
                    case 'inicio':
                        $where .= " AND inicio='$value'";
                        break;
                    case 'inicio_min':
                        $where .= " AND inicio>='$value'";
                        break;
                    case 'inicio_max':
                        $where .= " AND inicio<='$value'";
                        break;
                    case 'fin':
                        $where .= " AND fin='$value'";
                        break;
                    case 'fin_min':
                        $where .= " AND fin>='$value'";
                        break;
                    case 'fin_max':
                        $where .= " AND fin<='$value'";
                        break;
                }
            }
        }

        $sql = "SELECT * FROM cortes $where";
        $resultado = $database->query( $sql );

        while ( $row = mysqli_fetch_array( $resultado ) ) {
            $array_return[] = new model( $row );
        }

        return $array_return;
    }

    /**
     * Funcion para completar los atributos de la clase a partir de un array asociativo.
     *
     * @param Array $attributes Array con los atributos que se quieren poner
     */
    private function set( $attributes ) {
        if ( isset( $attributes[ 'corteID' ] ) ) {
            $this->id = $attributes[ 'corteID' ];
        }
        if ( isset( $attributes[ 'titulo' ] ) ) {
            $this->titulo = $attributes[ 'titulo' ];
        }
        if ( isset( $attributes[ 'direccion' ] ) ) {
            $this->direccion = $attributes[ 'direccion' ];
        }
        if ( isset( $attributes[ 'inicio' ] ) ) {
            $this->inicio = $attributes[ 'inicio' ];
        }
        if ( isset( $attributes[ 'fin' ] ) ) {
            $this->fin = $attributes[ 'fin' ];
        }
        if ( isset( $attributes[ 'motivo' ] ) ) {
            $this->motivo = $attributes[ 'motivo' ];
        }
        if ( isset( $attributes[ 'tramo' ] ) ) {
            $this->tramo = $attributes[ 'tramo' ];
        }
        if ( isset( $attributes[ 'observaciones' ] ) ) {
            $this->observaciones = $attributes[ 'observaciones' ];
        }
        if ( isset( $attributes[ 'latitud' ] ) ) {
            $this->latitud = $attributes[ 'latitud' ];
        }
        if ( isset( $attributes[ 'longitud' ] ) ) {
            $this->longitud = $attributes[ 'longitud' ];
        }
        if ( isset( $attributes[ 'pares' ] ) ) {
            $this->pares = $attributes[ 'pares' ];
        }
        if ( isset( $attributes[ 'impares' ] ) ) {
            $this->impares = $attributes[ 'impares' ];
        }
    }

    /**
     * Funcion para pasar los campos extraidos del rss a textos normales (quita todo lo que sobra)
     *
     * @param String $str Cadena obtenida del feed
     *
     * @return String limpio
     */
    private function sanitize( $str ) {
        $str = utf8_decode( $str );
        $str = html_entity_decode( $str );
        $str = explode( ':', $str, 2 );
        if ( is_array( $str ) && sizeof( $str ) == 2 ) {
            $str = $str[ 1 ];
        } elseif ( is_array( $str ) ) {
            $str = reset( $str );
        }
        $str = trim( $str );

        // Comprobamos si es una fecha
        if ( substr( $str, 2, 1 ) == '/' && substr( $str, 5, 1 ) == '/' ) {
            $str = explode( ' ', $str );
            $str = reset( $str ); // dividimos
            // Es una fecha, puede ser dd/mm/aa o dd/mm/aaaa, si es aa sera xx, si es aaaa sera 20xx
            if ( substr( $str, 6, 2 ) != '20' ) {
                $str = explode( ' ', $str );
                $str = reset( $str );

                // Dividimos por /
                $str = explode( '/', $str );
                $dia = '01';
                $mes = '01';
                $year = '1970';
                // Comprobamos el primero
                if ( $str[ 0 ] > 0 && $str[ 0 ] < 32 ) {
                    $dia = $str[ 0 ];
                }
                // Comprobamos el segundo
                if ( $str[ 1 ] > 0 && $str[ 1 ] < 13 ) {
                    $mes = $str[ 1 ];
                }
                // Comprobamos el tercero
                if ( $str[ 2 ] > 2000 && $str[ 2 ] < 2100 ) {
                    $year = $str[ 2 ];
                } else {
                    // Puede que tenga un digito duplicado
                    if ( substr( $str[ 2 ], 0, 4 ) > 2000 && substr( $str[ 2 ], 0, 4 ) < 2100 ) {
                        $year = substr( $str[ 2 ], 0, 4 );
                    } elseif ( substr( $str[ 2 ], 1, 4 ) > 2000 && substr( $str[ 2 ], 1, 4 ) < 2100 ) {
                        $year = substr( $str[ 2 ], 1, 4 );
                    }
                }
                $str = "$dia/$mes/$year";
            }
            // Sustituimos / por - para compatibilidad y evitar errores
            $str = str_replace( '/', '-', $str );
        }

        return $str;
    }

    /**
     * Almacena una incidencia en la base de datos
     */
    function store() {
        $database = new database();

        // Fecha de inicio
        $inicio = strtotime( $this->inicio );
        $inicio = date( 'Y-m-d H:i:s', $inicio );
        // Fecha de finalizacion
        $fin = strtotime( $this->fin );
        $fin = date( 'Y-m-d H:i:s', $fin );

        // SQL Injection
        $id = mysql_escape_string( $this->id );
        $titulo = mysql_escape_string( $this->titulo );
        $direccion = mysql_escape_string( $this->direccion );
        $motivo = mysql_escape_string( $this->motivo );
        $tramo = mysql_escape_string( $this->tramo );
        $observaciones = mysql_escape_string( $this->observaciones );
        $latitud = mysql_escape_string( $this->latitud );
        $longitud = mysql_escape_string( $this->longitud );

        if ( $this->id != -1 ) {
            $sql = "REPLACE cortes (corteID,titulo,direccion,inicio,fin,motivo,tramo,observaciones,latitud,longitud,pares,impares) VALUES " .
                "('" . $this->id . "','" . $this->titulo . "','" . $this->direccion . "','" . $inicio . "','" . $fin . "','" . $this->motivo . "','" . $this->tramo . "','" . $this->observaciones . "','" . $this->latitud . "','" . $this->longitud . "','" . $this->pares . "','" . $this->impares . "')";
        } else {
            $sql = "REPLACE cortes (titulo,direccion,inicio,fin,motivo,tramo,observaciones,latitud,longitud,pares,impares) VALUES " .
                "('" . $this->titulo . "','" . $this->direccion . "','" . $inicio . "','" . $fin . "','" . $this->motivo . "','" . $this->tramo . "','" . $this->observaciones . "','" . $this->latitud . "','" . $this->longitud . "','" . $this->pares . "','" . $this->impares . "')";
        }
        $database->query( $sql );
    }

    // Accesores
    public function get_direccion() { return $this->direccion; }

    public function get_fin() { return $this->fin; }

    public function get_id() { return $this->id; }

    public function get_impares() { return $this->impares; }

    public function get_inicio() { return $this->inicio; }

    public function get_latitud() { return $this->latitud; }

    public function get_longitud() { return $this->longitud; }

    public function get_motivo() { return $this->motivo; }

    public function get_observaciones() { return $this->observaciones; }

    public function get_pares() { return $this->pares; }

    public function get_titulo() { return $this->titulo; }

    public function get_tramo() { return $this->tramo; }

    /**
     * Devuelve una descripcion completa en HTML (para burbujas y demas)
     */
    public function get_descripcion() {
        setlocale( LC_ALL, 'es_ES' );
        $formato = '%A %e de %B de %Y, a las %H:%M';
        $inicio = strftime( $formato, strtotime( $this->inicio ) );
        $fin = strftime( $formato, strtotime( $this->fin ) );

        $str = '<h1>' . $this->titulo . '</h1>';
        $str .= '<table><tr><td><span class="b">Inicio:</td><td>' . $inicio . '</td></tr>';
        $str .= '<tr><td><span class="b">Fin:</td><td>' . $fin . '</td></tr>';
        $str .= '<tr><td colspan="2"><span class="b">Observaciones:</td></tr><tr><td colspan="2">' . $this->observaciones . '</td></tr></table>';

        return $str;
    }
}

