<?php
namespace ORM;


abstract class Entity
{
    
    private $pkName = 'rowid';
    protected $displayNames =[]; 
    private $bindTypes=[];
    private $columnDefinitions=[];

    public function getPkName(): string
    {
        return $this->pkName;
    }

    public function getClassName(){
        return get_class($this);
    }

    public function getDisplayName($field){
        return isset($this->displayNames[$field])? $this->displayNames[$field] : $field;
    }

    public function getBindType($field){
        return isset($this->bindTypes[$field])? $this->bindTypes[$field] : SQLITE3_TEXT;
    }

    public function getColumnDefinitions()
    {
        return $this->columnDefinitions;
    }

    /***
     * Searches through entity columnDefinitions for fields containing 'AUTOINCREMENT'
     * as column constraints, and return an indexed array of these fields, or an empty
     * array if none are found
     * @return array - an array that contains any fields which are defined
     * as AUTOINCREMENT, or empty array if none found
     */
    public function getAutoIncrementColumnDefinitions()
    {

        //Iterates through entity columnDefinitions array and searches for AUTOINCREMENT
        //in the value of each array row.  If found, adds the row to result array
        //and returns it
        $result = preg_grep('/AUTOINCREMENT/i', $this->columnDefinitions);
        return $result? $result : [];  //Returns result array or empty array if result is empty
    }

    /***
     * @param $field - the field name
     * @param $type - the SQL datatype of the column
     * @param string $description - any addition SQL constraints
     * This function takes in an entity field, a string representing its SQL datatype, and a string of SQL constraints
     * to add them to the entity's associate arrays columnDefinitions and bindTypes
     * Description is given a default value of an empty string if no constraints passed in
     */
    protected function addColumnDefinition($field,$type,$description=''){

        //Ensure field is all uppercase
        $type = strtoupper($type);
        //Ensure description is all uppercase
        $description = strtoupper($description);

        //If description contains 'PRIMARY KEY', use field as Entity's primary key name
        if(strpos($description,'PRIMARY KEY')>-1){$this->pkName=$field;}

        //Set the entities columnDefinition associative array
        //Using the passed in field as the index, build the SQL column definition
        //with the fieldName, datatype, and constraint description
        $this->columnDefinitions[$field] = "$field $type $description";

        $bindType=null;
        //Delimit the datatype to the first '(' found as the switch
        //Use delimited datatype to set the sqLite bindType
        //Default to SQLITE_TEXT
        switch ( strtok($type,'(') )
        {
            case 'INTEGER':
            case 'BIGINT':
            case 'BOOLEAN':
                $bindType = SQLITE3_INTEGER;
                break;

            case 'REAL':
            case 'FLOAT':
            case 'DECIMAL':
            case 'DOUBLE':
                $bindType =  SQLITE3_FLOAT;
                break;

            case 'BLOB':
                $bindType =  SQLITE3_BLOB;
                break;

            default:
                $bindType =  SQLITE3_TEXT;
                break;
        }

        //Set associative array of Entity's bindType using
        //current field as the index, and the bindType from the
        //switch as the value
        $this->bindTypes[$field] = $bindType;
    }

    public function parseArray($array)
    {
         foreach (array_intersect_key($array, get_object_vars($this)) as $field=>$valFromArray){
            $this->$field = $valFromArray;
        }
        return $this;
    }

    public function validate()
    {
        $errorsArray =[];
        foreach (get_class_methods($this) as $functionName){
            if(strpos($functionName,'validate_')===0) {
                $errorsArray = array_merge($errorsArray, call_user_func([$this, $functionName]));
            }
        }
        return $errorsArray;
    }

}