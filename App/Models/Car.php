<?php

namespace Auto\Models;

use Auto\Base;

class Car extends Base
{
    private $id;
    private $type;
    private $age;
    private $technical_exam_year;
    private $owner_id;


    /**
     * @return Car[]
     */
    public static function all()
    {
        $cars = [];

        $query = self::$mysql->query("SELECT id FROM cars");

        while ($row = $query->fetch_assoc()) {
            $cars[] = new Car($row['id']);
        }

        return $cars;
    }

    /**
     * @param $id
     * @return Car
     */
    public static function find($id)
    {
        return new Car($id);
    }

    public static function search($search_value)
    {
        $query = self::$mysql->query("SELECT id FROM cars WHERE type LIKE '%$search_value%'");

        $cars = [];

        while($row = $query->fetch_assoc()){
            $cars[] = new Car($row['id']);
        }

        return $cars;

    }

    public function __toString(){
        return "$this->id";
    }

    /**
     * Car constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $query = self::$mysql->query("SELECT * FROM cars WHERE id='$id'");

        if ($query == null || $query->num_rows != 1) {
            return false;
        }

        $results = $query->fetch_assoc();

        $this->id = $results['id'];
        $this->type = $results['type'];
        $this->age = $results['age'];
        $this->technical_exam_year = $results['technical_exam_year'];
        $this->owner_id = $results['owner_id'];

        return true;
    }

    /**
     * @param $type
     * @param $age
     * @param $technical_exam_year
     * @param User $owner
     * @return bool
     */
    public static function create($type, $age, $technical_exam_year, User $owner)
    {
        $type = self::$mysql->real_escape_string($type);
        $age = self::$mysql->real_escape_string($age);
        $technical_exam_year = self::$mysql->real_escape_string($technical_exam_year);
        $owner_id = $owner->getId();

        self::$mysql->query("INSERT INTO cars (type, age, technical_exam_year, owner_id) VALUES ('$type', '$age', '$technical_exam_year', '$owner_id')");

        return true;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getAge()
    {
        return $this->age;
    }

    public function getTechnicalExamYear()
    {
        return $this->technical_exam_year;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setAge($age)
    {
        if(!is_numeric($age)){
            return false;
        }else{
            $this->age = $age;
            return true;
        }
    }

    public function setTechnicalExamYear($technical_exam_year)
    {
        if(!is_numeric($technical_exam_year)){
            return false;
        }else{
            $this->technical_exam_year = $technical_exam_year;
            return true;
        }
    }

    public function setOwner($user){
        if(User::find($user) != null){
            $this->owner_id = $user;
            return true;
        }else{
            return false;
        }
    }

    public function save()
    {
        self::$mysql->query("UPDATE cars SET type='$this->type', age='$this->age', technical_exam_year='$this->technical_exam_year', owner_id='$this->owner_id' WHERE id='$this->id'");
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return new User($this->owner_id);
    }

    public function delete()
    {
        self::$mysql->query("DELETE FROM cars WHERE id='$this->id'");
        self::$mysql->query("DELETE FROM services WHERE car_id='$this->id'");

        return true;
    }
}