<?php
    use cri2net\php_pdo_db\PDO_DB;


    $is_terminal = false;
    $is_webcam   = false;

    if (isset($_GET['by']) && ($_GET['by'] == 'filter')) {
        
        $is_terminal = isset($_GET['is_terminal']);
        $is_webcam   = isset($_GET['is_webcam']);
    }

    $where = (isset($__service_centers_where)) ? $__service_centers_where : '';
    $list = PDO_DB::table_list(TABLE_PREFIX . 'service_centers', $where);
    $markers = [];
    
    foreach ($list as $sc) {

        $id = $sc['id'];
        $region = PDO_DB::row_by_id(TABLE_PREFIX . 'dict_regions', $sc['id_region']);
        $latitude = $sc['latitude'];
        $longtitude = $sc['longtitude'];
        $title = htmlspecialchars($region['title']) . ' р-н';
        $address = str_replace("'", "\'", $sc['address']);
        
        if (!isset($sc['is_online'])) {
            $sc['is_online'] = false;
        }

        if ($latitude && $longtitude) {
            $markers[] = "createMarker('$latitude', '$longtitude'," . ($sc['is_online'] ? 'true' : 'false') . " ,'$title', '<div style=\"max-width:350px;\"><h3>$title</h3>$address</div>')";
        }
    }
?>
<input type="hidden" id="google-maps-route-end" />
<input type="hidden" id="google-maps-route-start" />
<div class="about-form">
    <div class="row">
        <div class="col-md-8">
            <div class="map-about matchHeight" id="map_canvas" data-lat="50.4359167" data-lng="30.5420269" data-zoom="17"></div>
        </div>
        <div class="col-md-4">
            <div class="info matchHeight">
                <form action="<?= BASE_URL; ?>/service-centers/" method="get">
                    <input type="hidden" name="by" value="filter">
                    <!-- <div class="dropdown">
                      <button class="input-green no-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        Оберіть район
                        <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu disabled">
                        <?php
                            $regions = PDO_DB::table_list(TABLE_PREFIX . 'dict_regions');
                            foreach ($regions as $r) {
                                ?><li><a data-value="<?= $r['id'] ?>"><?= $r['title'] ?></a></li><?php
                            }
                        ?>
                      </ul>
                    </div> -->
                    <label class="checkbox black black-label">
                        <input <?= ($is_webcam) ? 'checked="checked"' : ''; ?> name="is_webcam" value="1" type="checkbox" class="">
                        <span>Веб камера</span>
                    </label>
                    <label class="checkbox black black-label">
                        <input <?= ($is_terminal) ? 'checked="checked"' : ''; ?> name="is_terminal" value="1" type="checkbox" class="">
                        <span>Термінал для сплати</span>
                    </label>

                    <input type="hidden" id="service-centers-region" value="<?= $_region; ?>" name="region" />
                    <div class="dropdown">
                      <button class="select-white dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" id="select-office" name="office" value="1">
                        <?= ($_region > 0) ? htmlspecialchars($current_region['title']) : 'Оберіть район'; ?>
                        <span class="caret"></span>
                      </button>
                        <ul class="dropdown-menu" aria-labelledby="select-office">
                            <li><a id="service-centers-region-a-0" onclick="$('#service-centers-region').val('0');" data-value="0">Оберіть офіс</a></li>
                            <?php
                                foreach ($regions as $region) {
                                    ?>
                                    <li><a id="service-centers-region-a-<?= $region['id']; ?>" onclick="$('#service-centers-region').val('<?= $region['id']; ?>');" data-value="<?= $region['id']; ?>"><?= htmlspecialchars($region['title']); ?></a></li>
                                    <?php
                                }
                            ?>
                        </ul>
                        <script>
                            $(document).ready(function(){
                                $('#service-centers-region-a-<?= $_company; ?>').click();
                            });
                        </script>
                    </div>


                    <input type="submit" class="btn btn-green btn-green-white" value="Пошук відділень">
                    <a href="<?= BASE_URL; ?>/service-centers/" class="all">перейти до списку всіх відділень</a>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function initSCMap()
    {
        var myLatlng = new google.maps.LatLng(46.551878, 33.6858659);
        var myOptions = {
            zoom: 8,
            center: myLatlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            mapTypeControl: false
        }
        map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

        function createMarker(lat, lon, online, title, html) {
            var newmarker = new google.maps.Marker({
                position: new google.maps.LatLng(lat, lon),
                map: map,
                //icon: online ? '/pic/placemark-online.png' : '/pic/placemark-offline.png',
                title: title
            });
            newmarker['infowindow'] = new google.maps.InfoWindow({
                    content: html
                });
            google.maps.event.addListener(newmarker, 'click', function() {
                this['infowindow'].open(map, this);
                if (typeof currentInfoWindow == "object") {
                    currentInfoWindow.close();
                }
                currentInfoWindow = this['infowindow'];

            });
            return newmarker;
        }

        //Add a marker clusterer to manage the markers.
        var markers = [<?= implode(",", $markers); ?>];
        var bounds = new google.maps.LatLngBounds();
        for (var i = 0; i < markers.length; i++) {
            bounds.extend(markers[i].getPosition());
        }

        map.fitBounds(bounds);
    }

    $(function(){
        initSCMap();
    });
</script>
