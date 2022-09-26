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
 * Contiene las funciones para leer el xml
 *
 * @author Victor Corbacho <victor@victorcorbacho.com>
 * @version 1.0.0 - 07/05/2011
 * @package
 * @license http://www.gnu.org/licenses/gpl-3.0.html
 */

require_once( 'libs/rss_fetch.inc' );

class model_incident_xml {

    /**
     * Fecha de inicio
     */
    protected $fecha_inicio = '';
    /**
     * Fecha de finalizacion
     */
    protected $fecha_fin = '';
    /**
     * Indica si se obtendran las coordenadas compatibles con google maps o no
     */
    protected $wgs84 = true;
    /**
     * URL del servicio
     */
    private $url = 'http://www.zaragoza.es/georref/rdf/hilo/verHistorico_Incidencias?id=0&fechainicio=%s&fechafin=%s&srsname=%s';
    /**
     * Documento xml cargado
     */
    protected $xml = '';

    function __construct( $fecha_inicio = '', $fecha_fin = '', $wgs84 = true ) {
        if ( $fecha_inicio !== '' ) {
            $this->fecha_inicio = $fecha_inicio;
        }
        if ( $fecha_fin !== '' ) {
            $this->fecha_fin = $fecha_fin;
        }
        $this->wgs84 = $wgs84;
        $this->load_xml();
    }

    function load_xml() {
        $wgs84 = $this->wgs84 ? 'wgs84' : '';
        $this->url = sprintf( $this->url, $this->fecha_inicio, $this->fecha_fin, $wgs84 );
        if ( $this->xml = simplexml_load_string( file_get_contents( $this->url ) ) ) {
            $xml = $this->xml;
            foreach ( $xml->item as $incidencia ) {
                $incidencia = new model_incident( $incidencia );
                $incidencia->store();
            }
        }

    }
}
