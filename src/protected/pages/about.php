<?php
    use cri2net\php_pdo_db\PDO_DB;

    $list = PDO_DB::table_list(TABLE_PREFIX . 'service_centers');
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
<body>

<?php require_once(PROTECTED_DIR . '/layouts/navbar_inner.php'); ?>
<?php require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php'); ?>

<div class="container">
    <content>
        <div class="text">
            <h1>Про нас</h1>
            <p>
                Основною метою діяльності Концерну – є відкриття та організація роботи сервісних центрів для обслуговування споживачів з усіх питань надання житлово-комунальних послуг. Створення мережі сервісних центрів – це, в першу чергу, необхідність стандартизувати роботу усіх точок прийому споживачів з питань ЖКГ, які на сьогодні працюють. Організація роботи центрів - це потреба часу та змін, які відбулися в системі визначення нових виконавців послуг. Створення таких центрів комунального сервісу сприятиме підвищенню та покращенню сфери обслуговування споживачів у частині отримання консультацій та інформації з усіх питань ЖКГ. Основний акцент сервісних центрів робиться на наданні професійних консультацій споживачам з питань житлово-комунального господарства. Новий сервіс також передбачає підвищення якості стандартів обслуговування, а саме:
                <ul>
                    <li>ввічливе ставлення до споживачів</li>
                    <li>швидкість та якість надання консультацій</li>
                    <li>створення комфортних умов очікування</li>
                    <li>мінімальні черги</li>
                    <li>Пріоритетним також буде створення нових форматів обслуговування:</li>
                    <li>можливість попереднього запису до спеціалістів за телефоном або через інтернет (економія часу й зусиль)</li>
                    <li>отримання інформації «не виходячи з дому» (по телефону, електронною поштою тощо)</li>
                    <li>Заступник генерального директора з адміністративно-господарської діяльності</li>
                </ul>
            </p>
        </div>

        <div class="about-form">
            <div class="row">
                <div class="col-md-8">
                    <div class="map-about matchHeight" id="map_canvas" data-lat="50.4359167" data-lng="30.5420269" data-zoom="17"></div>
                </div>
                <div class="col-md-4">
                    <div class="info matchHeight">
                        <form action="<?= BASE_URL; ?>/service-centers/" method="get">
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
                                <input checked="checked" name="has_webcam" type="checkbox" class="">
                                <span>Веб камера</span>
                            </label>
                            <label class="checkbox black black-label">
                                <input checked="checked" name="has_terminal" type="checkbox" class="">
                                <span>Термінал для сплати</span>
                            </label>
                            <input type="submit" class="btn btn-green btn-green-white" value="Пошук відділень">
                            <a href="<?= BASE_URL; ?>/service-centers/" class="all">перейти до списку всіх відділень</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </content>
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