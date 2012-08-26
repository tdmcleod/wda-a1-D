<?php

include('utilities.php');

function process_form($form_array) {
    $fields_array = array
        (
        "wine_name" => " LIKE ",
        "winery_name" => " LIKE ",
        "region_name" => " LIKE ",
        "variety" => " LIKE ",
        "stock" => " >= ",
        "order" => " >= ",
        "year_min" => " >= ",
        "year_max" => " <= ",
        "max_price" => " <= ",
        "min_price" => " >= "
    );

    $query_terms = "";
    $having_terms = "";
    foreach ($form_array as $key => $value) {

        if (array_key_exists($key, $fields_array) && $value != "") {

            
            
            switch ($key) {
                case 'year_min': $query_terms.=' AND wine.year >= ' . $value;
                    break;

                case 'year_max': $query_terms.=' AND wine.year <= ' . $value;
                    break;

                case 'max_price': $query_terms.=' AND inventory.cost <=' . $value;
                    break;

                case 'min_price':$query_terms.=' AND inventory.cost >=' . $value;
                    break;

                case 'stock':$query_terms.=' AND inventory.on_hand >=' . $value;
                    break;

                case 'order':$having_terms.='|HAVING SUM(items.qty) >=' . $value.";";
                    break;

                case 'wine_name': $query_terms.=' AND wine.wine_name LIKE \'%' . $value . '%\'';
                    break;

                case 'winery_name': $query_terms.=' AND winery.winery_name LIKE \'%' . $value . '%\'';
                    break;

                case 'region_name': $query_terms.=' AND region.region_name LIKE \'%' . $value . '%\'';
                    break;

                case 'variety': $query_terms.=' AND grape_variety.variety LIKE \'%' . $value. '%\'';
                    break;
            }
        }
    }
    if($having_terms!=""){
        $query_terms.=$having_terms;
    }
    return $query_terms;
}



function get_wine_info($search_params) {
    $conn = connect();

    $params=explode("|", $search_params);
    $sql = "SELECT SUM(items.qty) as total_qty, SUM(items.price) as total_revenue, wine.wine_name,inventory.cost, items.price, items.qty, inventory.on_hand, 
        winery.winery_name, region.region_name,wine.year, grape_variety.variety   
    FROM wine,inventory,items, winery, region,grape_variety, wine_variety
    WHERE (wine.wine_id = items.wine_id AND wine.wine_id = inventory.wine_id 
    AND winery.winery_id = wine.winery_id AND winery.region_id = region.region_id AND wine.wine_id=wine_variety.wine_id 
    AND grape_variety.variety_id=wine_variety.variety_id" . 
    $params[0] . ") GROUP BY wine.wine_id ".$params[1];
    
    $idx = 1;
    
    echo "<table><thead>
            <tr><th style='text-align:left;width:60px;'></th>
                <th>Wine Name</th>
                <th>Variety</th>        
                <th>Year</th>
                <th>Winery Name</th>
                <th>Region Name</th>
                <th>Inventory Cost</th>
                <th>Stock on Hand</th>
                <th>Quantity Sold</th>
                <th>Total Revenue</th>
                
                
                
                
            </tr>
            </thead>
            <tbody>

        ";
    
    
    
        $res = $conn->query($sql);
    $r = $res->fetchAll();
   if(count($r)==0)
   {
            echo "<tr><td>No Results Found</td></tr>";
    }
    else
    {
    
        foreach ($conn->query($sql) as $results) {


            echo "<tr>
                <td style='text-align:left;width:60px;'>$idx</td>
                <td>$results[wine_name]</td>
                <td>$results[variety]</td>
                <td>$results[year]</td>
                <td>$results[winery_name]</td>
                <td>$results[region_name]</td>    
                <td>$results[cost]</td>
                <td>$results[on_hand]</td>
                <td>$results[total_qty]</td>
                <td>$results[total_revenue]</td>
                </tr>";

            $idx++;
        }
    
    } 
    echo "</tbody></table>";
}

include('header.php');
?>


<div class="main-container">
    <div class="main-inner">

        <div class="results-container">
            
            <?php get_wine_info(process_form($_POST)); ?>


        </div>

    </div>
</div>












