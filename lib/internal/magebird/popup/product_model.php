<?php
class product_model{

  public function getProductSql($productId,$storeId,$prefix,$pdo){  
      $entityFieldName = $this->getEntityFieldName($pdo,$prefix);
      $storeId = intval($storeId);
      $productId = intval($productId);
      $sql = "
      SELECT * FROM (
            SELECT
                ce.sku,
                ea.attribute_id,
                ea.attribute_code,
                CASE ea.backend_type
                   WHEN 'varchar' THEN ce_varchar.value
                   WHEN 'int' THEN ce_int.value
                   WHEN 'text' THEN ce_text.value
                   WHEN 'decimal' THEN ce_decimal.value
                   WHEN 'datetime' THEN ce_datetime.value
                   ELSE ea.backend_type
                END AS value,
                ea.is_required AS required,
                EAOV.value AS option_value
            FROM ".$prefix."catalog_product_entity AS ce
            LEFT JOIN ".$prefix."eav_attribute AS ea
                ON ea.entity_type_id = 4
            LEFT JOIN ".$prefix."catalog_product_entity_varchar AS ce_varchar
                ON ce.".$entityFieldName." = ce_varchar.".$entityFieldName."
                AND ea.attribute_id = ce_varchar.attribute_id
                AND ea.backend_type = 'varchar'
                AND ce_varchar.store_id=$storeId
            LEFT JOIN ".$prefix."catalog_product_entity_int AS ce_int
                ON ce.".$entityFieldName." = ce_int.".$entityFieldName."
                AND ea.attribute_id = ce_int.attribute_id
                AND ea.backend_type = 'int'
                AND ce_int.store_id=$storeId
            LEFT JOIN ".$prefix."catalog_product_entity_text AS ce_text
                ON ce.".$entityFieldName." = ce_text.".$entityFieldName."
                AND ea.attribute_id = ce_text.attribute_id
                AND ea.backend_type = 'text'
                AND ce_text.store_id=$storeId
            LEFT JOIN ".$prefix."catalog_product_entity_decimal AS ce_decimal
                ON ce.".$entityFieldName." = ce_decimal.".$entityFieldName."
                AND ea.attribute_id = ce_decimal.attribute_id
                AND ea.backend_type = 'decimal'
                AND ce_decimal.store_id=$storeId
            LEFT JOIN ".$prefix."catalog_product_entity_datetime AS ce_datetime
                ON ce.".$entityFieldName." = ce_datetime.".$entityFieldName."
                AND ea.attribute_id = ce_datetime.attribute_id
                AND ea.backend_type = 'datetime' 
                AND ce_datetime.store_id=$storeId
            LEFT JOIN ".$prefix."eav_attribute_option EAO ON EAO.attribute_id = ea.attribute_id AND ce_int.value=EAO.option_id
            LEFT JOIN ".$prefix."eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id AND EAOV.store_id=0
            WHERE ce.".$entityFieldName." = $productId 
          ) AS tab";   
          return $sql;
  }
  
  public function getEntityFieldName($pdo,$prefix){          
      $result = $pdo->query("select * from ".$prefix."catalog_product_entity_varchar limit 1");
      $fields = array_keys($result->fetch(PDO::FETCH_ASSOC));
      if(in_array("entity_id", $fields)){
        return 'entity_id';
      }else{
        return 'row_id';
      }  
  }
  public function getProduct($productId,$storeId,$pdo,$prefix,$helper){              
    if(!$productId) return false;
    if(isset($this->products[$productId])) return $this->products[$productId];
    
    $sql = $this->getProductSql($productId,$storeId,$prefix,$pdo);          
    $results = $pdo->query($sql);
    $results->setFetchMode(PDO::FETCH_ASSOC);    
    $product = array(); 
    foreach ($results as $key => $row) {      
      if($row['attribute_code']=='small_image' || $row['attribute_code']=='thumbnail'){
        $product[$row['attribute_id']]['value'] = $helper->getParam('baseUrl')."/pub/media/catalog/product/".$row['value'];      
      }elseif($row['attribute_code']=='sku'){
        $product[$row['attribute_id']]['value'] = $row['sku'];
      }elseif($row['option_value']){        
        $product[$row['attribute_id']]['value'] = $row['option_value'];          
      }else{
        //skip this line, otherwise value will never be null and it will never 
        //take value from default store scope
        //if(is_null($row['value'])) $row['value'] = $row['default_value'];
        $product[$row['attribute_id']]['value'] = $row['value'];
      }        
      $product[$row['attribute_id']]['option_value'] = $row['option_value'];
      $product[$row['attribute_id']]['attribute_code'] = $row['attribute_code'];
    }
    
    if($storeId!=0){        
      $sql2 = $this->getProductSql($productId,0,$prefix,$pdo);   
      $results = $pdo->query($sql2);
      $results->setFetchMode(PDO::FETCH_ASSOC);    
      //var_dump(count($results));
      foreach ($results as $key => $row) {
        if($row['attribute_code']=='small_image' || $row['attribute_code']=='thumbnail'){
          $product[$row['attribute_id']]['value'] = $helper->getParam('baseUrl')."/pub/media/catalog/product/".$row['value'];      
        }elseif($row['attribute_code']=='sku'){
          $product[$row['attribute_id']]['value'] = $row['sku'];      
        }elseif($product[$row['attribute_id']]['value']==null){
          if($row['attribute_code']=='sku'){
            $product[$row['attribute_id']]['value'] = $row['sku'];
          }elseif($row['option_value']){
            $product[$row['attribute_id']]['value'] = $row['option_value'];
          }else{
            if(is_null($row['value']) && isset($row['default_value'])) $row['value'] = $row['default_value'];
            $product[$row['attribute_id']]['value'] = $row['value'];
          }      
          $product[$row['attribute_id']]['option_value'] = $row['option_value'];
        }  
      } 
    }    
    
    foreach($product as $attr => $val){
      $productData[$val['attribute_code']] = $val['value'];
    } 
    
    $productData['product_id'] = $productId;
    $productData['id'] = $productId;
    $this->products[$productId] = $productData;    
    return $productData;
  }
  
  function getProductCategories($productId,$storeId,$pdo,$prefix){
    if(!$productId) return false; 
    $sql = "SELECT DISTINCT category_id FROM ".$prefix."catalog_category_product WHERE product_id = ".intval($productId);
    $results = $pdo->query($sql);
    $results = $results->fetchAll(PDO::FETCH_COLUMN, 0);
    return $results;  
  }  
    
}