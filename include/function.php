<?php

//Tabulate query result
function queryResult($field, $index, $term, $address, $exact, $offset, $limit) {
    $num_result_per_page = 100;
    $table_text_len = 100;
    $link = mysqli_connect('localhost:3306', 'root', '1229@Oxford', 'digisigres');
    // $link = mysqli_connect('localhost:3306', 'digisig', '1EMeeIIINnn', 'digisigres');
    $pagination_part = ' limit ' . $limit . ' offset ' . $offset;
    // search 'what' and 'from'? 
    $query3 = "SELECT field_title, field_url, field_column, field_returnedvariables FROM field WHERE field_url = '$field'";
    $query3result = mysqli_query($link, $query3);
    $row = mysqli_fetch_object($query3result);
    $column = $row->field_column;
    $variables = $row->field_returnedvariables;
    
    if('' != $column or null != $column)
    {
        // search 'where'?
        $query4 = "SELECT a_index, fk_catalogue, fk_repository FROM tb_index WHERE index_url = '$index'";    
        $query4result = mysqli_query($link, $query4);
        $row = mysqli_fetch_object($query4result);
        $repository = $row->fk_repository;
        $catalogue = $row->fk_catalogue;
    
        //search how?
        if ($exact == "e") {
            $search = "= '$term'";
        }
        else {
            $search = "LIKE '%$term%'";
        }
    
        // make the SQL search string
        $query5 = "SELECT DISTINCT $variables FROM search_view WHERE ($column $search)";
    
    
        // Searching by *both* repository and catalogue is not supported -- choose one
        if ($repository > 0) {
            $query5 = $query5 . " AND (fk_repository = '$repository')";
        }
    
        if ($catalogue > 0) {
            $query5 = $query5 . " AND (fk_catalogue = '$catalogue')";
        }
    
        //and the ordering variable
        $query5 = $query5 . " ORDER BY $column";
        
        // the full search string applied
        $query5result = mysqli_query($link, $query5.$pagination_part);
    
        //test to see how many rows the query returned
        $numberofresults = mysqli_num_rows($query5result);
        //get total amout of results
        $query5count_result = mysqli_query($link, $query5);
        $count = mysqli_num_rows($query5count_result);
    
        //if there are returned rows (except from all_fields) then present output
    
        If ($field != "all_fields") {
            $field_str = ucfirst($field);
            If ($numberofresults > 0) {
                echo $count;
                if ($numberofresults > 1) {
                    echo " results found for " . $term;
                }
                else {
                    echo " result found for " . $term;
                }
                echo " in " . $field_str;
            
                //drawing the results in a tabular form
                echo '<table class="metaTable maxmin"><thead><th>#</th><th>'.$field_str.'</th><th>Reference</th></thead><tbody>';
                $rowcount = 1;
                while ($row = mysqli_fetch_array($query5result)) {
                    $value1 = $row[0];
                    $value2 = $row[1];
                    $value3 = $row[2];
                    
                    if($value1 == ""){
                        $value1 = "<i>empty</i>";
                    }
                    if($value2 == ""){
                        $value2 = "<i>empty</i>";
                    }
                    if($value3 == ""){
                        $value3 = "<i>empty</i>";
                    }
                    echo '<tr><td>' . $rowcount . '</td>'; //
                    if(strlen($value2) >= $table_text_len){
                        $short_value2 = substr($value2, 0, $table_text_len);
                        echo '<td><a id="a_'.$value1.'" href=' . $address . '/entity/'.$value1.'>'. $short_value2 . '...</a> <a id="get_'.$value1.'" onclick="getFullText('.$value1.')">(More)</a><input type="hidden" id="full_'.$value1.'" value="'.$value2.'" /><input type="hidden" id="short_'.$value1.'" value="'.$short_value2.'" /></td><td>'. $value3. '</td></tr>';
                    }else{
                        echo '<td><a id="a_'.$value1.'" href=' . $address . '/entity/'.$value1.'>'.$value2.'</a></td><td>'. $value3. '</td></tr>';
                    }
            
                    $rowcount++;
                }

                
                if($numberofresults < $count){
                    echo '<tr id="show_more_tr_'.$field.'" last_row_num='.$rowcount--.'><td colspan="3"><input type="button" id="show_more_btn_'.$field.'" value="Show More" offset='.($num_result_per_page+1).' onclick=\'getNextData("'.$field.'", "'.$index.'", "'.$term.'", "'.$address.'", "'.$exact.'", '.$limit.')\' /><span id="load_next_pending_'.$field.'" style="display:none">Loading...</span></td></tr></table>';    
                }else{
                    echo '</table>';
                }
            }
            else {echo "<p>no results in " . $field_str . "</p>";}
        }
    }
}



function queryview($entity, $id) {
    $link = mysqli_connect('localhost:3306', 'root', '1229@Oxford', 'digisigres');
    // $link = mysqli_connect('localhost:3306', 'digisig', '1EMeeIIINnn', 'digisigres');
     //convert view number to view text string and find out what variables to return
    $query6 = "SELECT entity_view_short, entity_column_short, entity_returnedvariables_short, entity_url FROM entity WHERE entity_url = '$entity'";
    $query6result = mysqli_query($link, $query6);
    $row = mysqli_fetch_object($query6result);
    $column = $row->entity_column_short;
    $view = $row->entity_view_short;
    $variables = $row->entity_returnedvariables_short;
    
    // the basic search string
    $query7 = "SELECT $variables FROM $view WHERE $column = $id";
    
    $queryviewresult = mysqli_query($link, $query7);
    return $query7;
}



#this function outputs a table listing seal descriptions
// the function can omit one description -- flagged by the $duplicate value

function sealdescription ($query12result, $address, $duplicate) {
    
    echo '<table class="metaTable"><thead><th>#</th><th>Name</th><th>Reference</th><th>External Link</th></thead><tbody>';
    $rowcount = 1;

while ($row = mysqli_fetch_array($query12result)) {
    $value1 = $row['a_index'];
    $value2 = $row['sealdescription_identifier'];
    $value3 = $row['id_sealdescription'];
    $value4 = $row['realizer'];
    if (isset($duplicate) && $value3 != $duplicate) { 
        echo '<tr><td> '. $rowcount . '</td>';
        echo '<td>' . $value4 . '</td>';
        echo '<td>' . $value1 . '</td>';
        echo '<td><a href="' . $address . '/entity/' . $value3. '">' . $value2 . '</a></td></tr>';
        $rowcount++;
    }
}
    echo "</tbody></table><br>";
}

?>