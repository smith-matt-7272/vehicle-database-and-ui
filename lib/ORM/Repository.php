<?php
namespace ORM;

spl_autoload_register(function($entityClassName){
    require_once '../' . $entityClassName . '.php';
});

class Repository extends \SQLite3
{
    private $lastStatement;

    public function getLastStatement()
    {
        return $this->lastStatement;
    }

    public function createTables($entities)
    {
        $results=[];
        foreach ($entities as $entity){
            $tableName = $entity->getClassName();
            $this->lastStatement = "DROP TABLE IF EXISTS $tableName";
            if(!$this->exec($this->lastStatement)){
                $results[$tableName] = 'ERROR: ' . $this->lastStatement;
            }else{
                $columnString = implode(',' . PHP_EOL, $entity->getColumnDefinitions());
                $this->lastStatement = "CREATE TABLE $tableName ($columnString)";
                $results[$tableName] = $this->exec($this->lastStatement) ? 1 : 'ERROR: ' . $this->lastStatement;
            }
        }
        return $results;
    }

    public function insert($entity) {
        if(count($entity->validate())){return -2;}

        $tableName = $entity->getClassName();
        $autoFields = $entity->getAutoIncrementColumnDefinitions();
        $filteredProperties = array_diff_key(get_object_vars($entity), $autoFields );

        $fieldString = implode(',', array_keys($filteredProperties));
        $qMarkString = str_repeat('?,', count($filteredProperties)-1) . '?';

        $this->lastStatement = "INSERT INTO $tableName ($fieldString) VALUES($qMarkString)";
        $stmt=$this->prepare($this->lastStatement);
        if(!$stmt){return -1;}

        $i = 1;
        foreach($filteredProperties as $field=>$val){
            $this->lastStatement .= " [$i:$field=$val] ";
            $stmt->bindValue($i++,$val,$entity->getBindType($field));
        }


        $result = $stmt->execute();
        if($result){
            $autoID = $this->lastInsertRowID();
            foreach (array_keys($autoFields) as $field){
                $entity->$field = $autoID;
            }
        }
        $stmt->close();
        return $result ? 1: 0;

    }

    //COMMENT ALL OF SELECT FUNCTION

    /**
     * MINICISE 35 - COMMENT ALL OF SELECT
     * @param $entity - entity to perform a select on
     * @param bool $inclusive - determine if select should be inclusive or exclusive - default exclusive
     * @return array|int - return an array if successful, an integer if not (per rule 5)
     */
    public function select($entity,$inclusive=false)
    {
        $tableName = $entity->getClassName();  //Pick the table
        $properties = get_object_vars($entity);  //Get the list of properties/columns from the entity

        //Take the associative keys from properties, convert to a string
        //separating each key with a comma
        $fieldString = implode(',', array_keys($properties));
        //All 'false' evaluated properties are filtered out and the remaining properties
        //are returned to the filterProperties
        $filteredProperties = array_filter($properties);
        //MINICISE 34 : Alter the WHERE clause so it can have wild cards in the values
        //EXAMPLE: '%Sm%' will match 'Smith' and 'Smitz'
        //If filteredProperties is empty, the WHERE string is 1=1 which will
        //return all entity records, otherwise the filterproperties
        //is used to build the wherestring
        //If select parameter 'inclusive' is set to true, each value is evaluated with OR operand,
        //otherwise evaluated with AND operand
        $whereString = empty($filteredProperties)? ' 1=1 ':
            implode($inclusive ? ' LIKE ? OR ' : ' LIKE ? AND ', array_keys($filteredProperties)) . ' LIKE ?';

        //Build the SQL statement
        $this->lastStatement = "SELECT $fieldString FROM $tableName WHERE $whereString";  //Rule 6
        $stmt=$this->prepare($this->lastStatement);  //Prepare the statement
        if(!$stmt){return -1;}  //Rule 5

        $i = 1;
        //Loop through each filteredProperty key/value
        foreach($filteredProperties as $field=>$val){
            $this->lastStatement .= " [$i:$field=$val] ";  //Rule 6
            $stmt->bindValue($i++,$val,$entity->getBindType($field));  //Bind the value from filteredProperty to the prepared statement
        }

        $result = $stmt->execute();  //Execute prepared statement and assign results
        if(!$result){  //Rule 5
            $stmt->close();  //Best practices
            return 0;
        }


        $entityArray=[];  //Instantiate entityArray
        //If results contains associative indexed records, loop
        //Otherwise skip
        while ($tableRow = $result->fetchArray(SQLITE3_ASSOC))
        {
            //Create an entity and parse the record from results to assign its property values
            //and add to entity array
            $entityArray[] = (new $tableName())->parseArray($tableRow);
        }
        $stmt->close();  //Best practices
        return $entityArray; //Return entity array (which may be empty)
    }


    public function update($entity)
    {
        if(count($entity->validate())){return -2;}
       
        $properties = get_object_vars($entity);
        $pkName = $entity->getPkName();

        unset($properties[$pkName]);
        $setString = implode('=?, ',array_keys($properties)).'=?';

        $this->lastStatement = "UPDATE {$entity->getClassName()} SET $setString WHERE $pkName=?";
        $stmt=$this->prepare($this->lastStatement);
        if(!$stmt){return -1;}

        $i = 1;
        foreach($properties as $field=>$val){
            $this->lastStatement .= " [$i:$field=$val] ";
            $stmt->bindValue($i++,$val,$entity->getBindType($field));
        }

        $this->lastStatement .= " [$i:$pkName={$entity->$pkName}] ";
        $stmt->bindValue($i,$entity->$pkName,$entity->getBindType($pkName));


        $result = $stmt->execute();
        $stmt->close();
        return $result ? 1: 0;

    }

    public function delete($entity){
        $pkName = $entity->getPkName();

        $this->lastStatement="DELETE FROM {$entity->getClassName()} WHERE $pkName=?";
        $stmt = $this->prepare($this->lastStatement);
        if(!$stmt){return -1;}

        $this->lastStatement .= " [1:$pkName={$entity->$pkName}] ";
        $stmt->bindValue(1,$entity->$pkName,$entity->getBindType($pkName));

        $result =$stmt->execute();
        $stmt->close();
        return $result ? 1: 0;
    }

}