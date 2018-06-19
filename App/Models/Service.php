<?php

namespace Auto\Models;

use Auto\Base;

class Service extends Base
{
    private $id;
    private $cost;
    private $description;
    private $fixer_id;
    private $car_id;

    /**
     * Service constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $query = self::$mysql->query("SELECT * FROM services WHERE id='$id'");

        if (!isset($query) || $query->num_rows != 1) {
            return false;
        }

        $results = $query->fetch_assoc();

        $this->id = $results['id'];
        $this->cost = $results['cost'];
        $this->description = $results['description'];
        $this->fixer_id = $results['fixer_id'];
        $this->car_id = $results['car_id'];

        return true;
    }

    /**
     * @param $id
     * @return Service
     */
    public static function find($id)
    {
        return new Service($id);
    }

    public function getId()
    {
        return $this->id;
    }

    public function delete()
    {
        self::$mysql->query("DELETE FROM services WHERE id='$this->id'");

        return true;
    }

    public function getCost()
    {
        return $this->cost;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return User
     */
    public function getFixer()
    {
        return new User($this->fixer_id);
    }

    /**
     * @return Car
     */
    public function getCar()
    {
        return new Car($this->car_id);
    }

    public function setCost($cost)
    {
        $cost = self::$mysql->real_escape_string($cost);

        self::$mysql->query("UPDATE services SET cost='$cost' WHERE id='$this->id'");

        return true;
    }

    public function setDescription($description)
    {
        $description = self::$mysql->real_escape_string($description);

        self::$mysql->query("UPDATE services SET description='$description' WHERE id='$this->id'");

        return true;
    }

    public function setFixer(User $user)
    {
        $new_id = $user->getId();

        self::$mysql->query("UPDATE services SET fixer_id='$new_id' WHERE id='$this->id'");
    }

    public function setCar(Car $car)
    {
        $new_id = $car->getId();

        self::$mysql->query("UPDATE services SET car_id='$new_id' WHERE id='$this->id'");
    }

    /**
     * @param Car $car
     * @param User $user
     * @param $cost
     * @param $description
     * @return bool
     */
    public static function create(Car $car, User $user, $cost, $description)
    {
        $cost = self::$mysql->real_escape_string($cost);
        $description = self::$mysql->real_escape_string($description);
        $fixer_id = $user->getId();
        $car_id = $car->getId();

        self::$mysql->query("INSERT INTO services (cost, description, fixer_id, car_id) VALUES ('$cost', '$description', '$fixer_id', '$car_id')");

        return true;
    }

    /**
     * @return Service[]|bool
     */
    public static function all()
    {
        $services = [];

        $query = self::$mysql->query("SELECT id FROM services");

        if ($query == null || $query->num_rows <= 0) {
            return false;
        }

        while ($row = $query->fetch_assoc()) {
            $services[] = new Service($row['id']);
        }

        return $services;
    }

    public function __toString(){
        return "$this->id";
    }
}