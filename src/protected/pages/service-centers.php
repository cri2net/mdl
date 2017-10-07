<?php
    use cri2net\php_pdo_db\PDO_DB;
?>
<body>

<?php require_once(PROTECTED_DIR . '/layouts/navbar_inner.php'); ?>
<?php require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php'); ?>

<div class="container">
    <content>

        <div class="cabinet-settings object-item object-item-bill">
            <form class="real-full-width-block" action="" method="post">
                <div class="thead-bg">
                    <div class="head-green"></div>
                    <div class="head-gray-2"></div>
                </div>                
                <div class="table-responsive">                    
                    <table class="full-width-table datailbill-table service-tableno-border" id="service-table">
                        <thead>
                            <tr class="head-green">
                                <th colspan="4" class="align-left">
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 col-sm-12">
                                            <div class="dropdown">
                                              <button class="select-white dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" id="select-office" name="office" value="1">
                                                Оберить офіс
                                                <span class="caret"></span>
                                              </button>
                                              <ul class="dropdown-menu" aria-labelledby="select-office">
                                                <li><a href="#" data-value="1">Головний офіс</a></li>
                                                <li><a href="#" data-value="2">Головний офіс</a></li>
                                                <li><a href="#" data-value="3">Головний офіс</a></li>
                                                <li><a href="#" data-value="4">Головний офіс</a></li>
                                              </ul>
                                            </div>                                            
                                        </div>
                                        <!-- <div class="col-lg-3 col-md-4 col-sm-12">
                                            <input type="text" placeholder="Ваша адреса" class="address input-white">
                                        </div> -->
                                        <div class="col-lg-3 col-md-4 col-sm-12">
                                            <div class="dropdown">
                                              <button class="select-white dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" id="select-company">
                                                Оберіть компанію
                                                <span class="caret"></span>
                                              </button>
                                              <ul class="dropdown-menu" aria-labelledby="select-company">
                                                <li><a href="#" data-value="1">ПАТ КИЇВЕНЕРГО</a></li>
                                                <li><a href="#" data-value="2">КП ГІОЦ</a></li>
                                                <li><a href="#" data-value="3">ДП КИЇВГАЗЕНЕРДЖИ</a></li>
                                              </ul>
                                            </div>                                           
                                            <!-- <a href="#" class="reset hidden-md hidden-sm hidden-ms hidden-xs">&times;</a> -->
                                        </div>
                                        <div class="col-lg-4 col-md-12 align-center">  
                                            <label class="checkbox black">
                                                <input checked="checked" value="inp_0" name="items[]" type="checkbox" class="">
                                                <span>веб камера</span>
                                            </label>
                                            <label class="checkbox black">
                                                <input checked="checked" value="inp_0" name="items[]" type="checkbox" class="">
                                                <span>термінал</span>
                                            </label>
                                            <a href="#" class="icon-settings"></a>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                            <tr class="head-gray-2">
                                <th>Район</th>
                                <th>Адреса</th>
                                <th>Графік роботи</th>
                                <th>Додаткові функції</th>
                            </tr>
                        
                        </thead>
                        <tbody>
                        <?php
                            $list = PDO_DB::table_list(TABLE_PREFIX . 'service_centers', null, "`id_region` ASC");

                            foreach($list as $item) {
                            $region = PDO_DB::row_by_id(TABLE_PREFIX . 'dict_regions', $item['id_region']);
                            $phone = strlen(trim($item['phone'])) < 3 ? '(044) 247-40-40' : trim($item['phone']);
                            $isWebcam = strlen(trim($item['webcam'])) > 0;
                            ?>
                            <tr class="item-row">
                                <td class="">     
                                    <?= $region['title']; ?>
                                    <div class="phone-more">
                                        <span class="phone"><?= $phone; ?></span>
                                        Дякуємо за звернення
                                    </div>
                                </td>
                                <td class="center">
                                    <?= $item['address']; ?>
                                </td>
                                <td class="center">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal<?= $item['id'] ?>" ><i class="fa fa-calendar"></i></button>
                                    <div class="modal fade" id="myModal<?= $item['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                      <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                          <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title" id="myModalLabel">Розклад роботи</h4>
                                          </div>
                                          <div class="modal-body" style="text-align: left;" ><?= nl2br($item['worktime']) ?></div>
                                          <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Закрити</button>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                </td>
                                <td class="center">
                                    <a href="#" class="icon icon-phone"></a>
                                    <a href="#" class="icon icon-map" data-id="<?= $item['id'] ?>"></a>
                                    <?php if($isWebcam){?><a href="#" class="icon icon-webcam"></a><?}?>
                                </td>
                            </tr>
                            <tr class="item-map">
                                <td colspan="4">
                                    <div class="div-shadow">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="map-service matchHeight" id="map-service-<?= $item['id'] ?>" data-lat="<?= $item['latitude'] ?>" data-lng="<?= $item['longtitude'] ?>" data-zoom="17"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info matchHeight">
                                                    <a href="#" class="close">&times;</a>
                                                    <span><?= $item['address'] ?><br>
                                                    Телефон:    <?= $phone ?></span>

                                                    <a href="#" class="icon-href icon-trace">Прокласти маршрут до відділення</a>
                                                    <a href="#" class="icon-href icon-web">Переглянути веб камеру у відділенні</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="item-web">
                                <td colspan="4">
                                    <div class="div-shadow">
                                        <div class="row">
                                            <div class="col-md-8 matchHeight"><?= $item['webcam'] ?></div>
                                            <div class="col-md-4">
                                                <div class="info matchHeight">
                                                    <a href="#" class="close">&times;</a>
                                                    <span><?= $item['address'] ?><br>
                                                    Телефон: <?= $phone ?></span>

                                                    <a href="#" class="icon-href icon-feedback"><span class="short">Залишити відгук</span></a>
                                                    <a href="#" class="icon-href icon-marker"><span class="short">Перейти до мапи</span></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>                                                     
                            <?
                            }
                        ?>   
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </content>
</div>

<div class="modal fade" id="modalCounterConfirm" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Видалити лічильник?</h3>
      </div>
      <div class="modal-body">
        <p>видалений лічильник неможливо відновити</p>
      </div>
      <div class="modal-footer">
        <div class="row">
            <div class="col-sm-6">
                <button type="button" class="btn btn-green btn-full btn-md" data-dismiss="modal">Назад</button>
            </div>
            <div class="col-sm-6">
                <button type="button" id="counter-delete-confirm" data-id="" class="btn btn-green-lighter btn-full btn-md">Видалити</button>
            </div>
        </div>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
