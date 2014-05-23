<?php
    /*
     *      Osclass – software for creating and publishing online classified
     *                           advertising platforms
     *
     *                        Copyright (C) 2012 OSCLASS
     *
     *       This program is free software: you can redistribute it and/or
     *     modify it under the terms of the GNU Affero General Public License
     *     as published by the Free Software Foundation, either version 3 of
     *            the License, or (at your option) any later version.
     *
     *     This program is distributed in the hope that it will be useful, but
     *         WITHOUT ANY WARRANTY; without even the implied warranty of
     *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *             GNU Affero General Public License for more details.
     *
     *      You should have received a copy of the GNU Affero General Public
     * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
     */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">
    <head>
        <?php osc_current_web_theme_path('head.php'); ?>
        <meta name="robots" content="index, follow" />
        <meta name="googlebot" content="index, follow" />
        <style>
        #main-map canvas{
            margin-top:2px;
        }
        #map-tooltip{
            position:absolute;
            background:#f2f2f2;
            border:solid 2px #bababa;
            margin-left:5px;
            margin-top:0px;
            padding:7px;
            border-radius:5px;
            -moz-border-radius: 5px;
            -webkit-border-radius: 5px;
        }
        </style>
    </head>
    <body>
        <?php osc_current_web_theme_path('header.php'); ?>
        <div class="form_publish">
            <?php osc_current_web_theme_path('inc.search.php'); ?>
        </div>
        <div class="content home">
            <div id="main">
                <?php osc_current_web_theme_path('inc.main.php') ; ?>
               <div class="latest_ads">
                    <h1><strong><?php _e('Latest Listings', 'usa'); ?></strong></h1>
                    <?php if( osc_count_latest_items() == 0) { ?>
                        <p class="empty"><?php _e('No Latest Listings', 'usa'); ?></p>
                    <?php } else { ?>
                        <table border="0" cellspacing="0">
                             <tbody>
                                <?php $class = "even"; ?>
                                <?php while ( osc_has_latest_items() ) { ?>
                                 <tr class="<?php echo $class. (osc_item_is_premium()?" premium":""); ?>">
                                        <?php if( osc_images_enabled_at_items() ) { ?>
                                         <td class="photo">
                                            <?php if( osc_count_item_resources() ) { ?>
                                                <a href="<?php echo osc_item_url(); ?>">
                                                    <img src="<?php echo osc_resource_thumbnail_url(); ?>" width="75" height="56" title="<?php echo osc_item_title(); ?>" alt="<?php echo osc_item_title(); ?>" />
                                                </a>
                                            <?php } else { ?>
                                                <img src="<?php echo osc_current_web_theme_url('images/no_photo.gif'); ?>" alt="" title="" />
                                            <?php } ?>
                                         </td>
                                        <?php } ?>
                                         <td class="text">
                                             <h3><a href="<?php echo osc_item_url(); ?>"><?php echo osc_item_title(); ?></a></h3>
                                             <p><strong><?php if( osc_price_enabled_at_items() && osc_item_category_price_enabled() ) { echo osc_item_formated_price(); ?> - <?php } echo osc_item_city(); ?> (<?php echo osc_item_region();?>) - <?php echo osc_format_date(osc_item_pub_date()); ?></strong></p>
                                             <p><?php echo osc_highlight( strip_tags( osc_item_description() ) ); ?></p>
                                         </td>
                                     </tr>
                                    <?php $class = ($class == 'even') ? 'odd' : 'even'; ?>
                                <?php } ?>
                            </tbody>
                        </table>
                        <?php if( osc_count_latest_items() == osc_max_latest_items() ) { ?>
                        <p class='pagination'><?php echo osc_search_pagination(); ?></p>
                            <p class="see_more_link"><a href="<?php echo osc_search_show_all_url();?>"><strong><?php _e("See all offers", 'usa'); ?> &raquo;</strong></a></p>
                        <?php } ?>
                    <?php View::newInstance()->_erase('items'); } ?>
                </div>
            </div>
            <div id="sidebar">
                <div class="navigation">
                    <?php if(osc_count_list_regions() > 0 ) { ?>
                    <div class="box location">
                        <h3><strong><?php _e("Location", 'usa'); ?></strong></h3>
                        <ul>
                        <?php while(osc_has_list_regions() ) { ?>
                            <li><a href="<?php echo osc_list_region_url(); ?>"><?php echo osc_list_region_name(); ?></a> <em>(<?php echo osc_list_region_items(); ?>)</em></li>
                        <?php } ?>
                        </ul>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <!-- map js -->
        <?php osc_current_web_theme_path('footer.php') ; ?>
        <script type="text/javascript" src="<?php echo osc_current_web_theme_js_url('jquery.maphilight.min.js') ; ?>"></script>
        <script type="text/javascript">
        $(function() {
            var linksRegions = new Array();
            var statsRegions = new Array();
        <?php
            $regions = json_decode(osc_get_preference('region_maps','usa'),true);
            if($regions){
                foreach($regions as $key => $value){
                    $regionData  = Region::newInstance()->findByPrimaryKey($value);
                    $regionStats = RegionStats::newInstance()->findByRegionId($value);
                    echo "    linksRegions['$key'] = '".map_region_url($value)."';".PHP_EOL;
                    echo "    statsRegions['$key'] = {name:'".osc_esc_js($regionData['s_name'])."', count:'".$regionStats['i_num_items']."'};".PHP_EOL;
                }
            }
        ?>
            //find all regions map has assigned a location
            $('area').each(function(){
                var $_hasClass  = $(this).attr('class'); //catching
                var $_index     = $('area:not([class^="group"])').index($(this));
                var colorStatus = true;

                $(this).click(function(){
                    var key = $_index;
                    if(typeof $_hasClass != 'undefined' && $_hasClass != ''){
                         key = $_hasClass;
                    }
                    if(typeof linksRegions[key] != 'undefined'){
                        window.location.href = linksRegions[key];
                    }
                    return false;
                }).hover(function(e){
                    var key     = $_index;
                    var canvas  = undefined;
                    if(typeof $_hasClass != 'undefined' && $_hasClass != ''){
                         key = $_hasClass;
                    }
                    if(typeof statsRegions[key] != 'undefined') {
                        $('#map-tooltip').html(statsRegions[key].name + ': '+statsRegions[key].count + ' ads').css({
                            top: e.pageY,
                            left: e.pageX
                        }).show();
                        canvas = document.getElementById("map-hover");
                        canvas.width = canvas.width;
                        options = {
                            lineColor:  '#de9200',
                            fillColor:  '#ffa800'
                        }
                        if($(this).attr('class') != '' && typeof $(this).attr('class') != 'undefined'){
                            $('.'+$(this).attr('class')).each(function(){
                                drawCanvas('map-hover', $(this).attr('coords'), options);
                            });
                        } else {
                            drawCanvas('map-hover', $(this).attr('coords'), options);
                        }
                    }
                },function(){
                    canvas       = document.getElementById("map-hover");
                    canvas.width = canvas.width;
                    $('#map-tooltip').hide();
                });
            });
        });
        </script>
        <div id="map-tooltip"></div>
        <!-- /map js -->
    </body>
</html>
