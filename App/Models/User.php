<?php

namespace App\Models;

use App\Base;

class User extends Base
{
    private $given_names;
    private $surname;
    private $id;
    private $email;
    private $date_of_birth;
    private $admin;
    private $password;
    private $salt;
    private $mechanic;

    /**
     * @return string
     */
    public static function generateSalt()
    {
        $characters = '0123456789qwertzuiopasdfghjklyxcvbnmQWERTZUIOPASDFGHJKLYXCVBNM';
        $chars_length = strlen($characters);
        $random = '';

        for ($i = 0; $i < 32; $i++) {
            $random .= $characters[rand(0, $chars_length - 1)];
        }

        return $random;
    }

    public static function find($id)
    {
        return new User($id);
    }

    /**
     * @param $email
     * @return User|null
     */
    public static function findByEmail($email)
    {
        $query = self::$mysql->query("SELECT id FROM users WHERE email='$email'");

        if ($query == null || $query->num_rows != 1) {
            return null;
        }

        $results = $query->fetch_assoc();

        return new User($results['id']);
    }

    /**
     * @param $email
     * @param $password
     * @return bool
     */
    public static function authenticate($email, $password)
    {
        $query = self::$mysql->query("SELECT password,salt FROM users WHERE email='$email'");

        if ($query != null && $query->num_rows == 1) {
            $results = $query->fetch_assoc();

            $db_password = $results['password'];
            $salt = $results['salt'];

            if (sha1($password . "loller" . $salt) == $db_password) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $email
     * @param $password
     * @param $given_names
     * @param $surname
     * @param $date_of_birth
     * @param $mechanic
     * @return bool
     */
    public static function create($email, $password, $given_names, $surname, $date_of_birth, $mechanic)
    {
        $email = self::$mysql->real_escape_string($email);
        $given_names = self::$mysql->real_escape_string($given_names);
        $surname = self::$mysql->real_escape_string($surname);
        $date_of_birth = self::$mysql->real_escape_string($date_of_birth);
        $mechanic = self::$mysql->real_escape_string($mechanic);

        $query = self::$mysql->query("SELECT email FROM users WHERE email='$email'");

        if ($query != null && $query->num_rows != 0) {
            return false;
        }

        if ($query->num_rows == 0) {
            $salt = self::generateSalt();
            $db_password = sha1($password . "loller" . $salt);


            self::$mysql->query("INSERT INTO users (email, password, given_names, surname, date_of_birth, salt, admin, mechanic) 
                                 VALUES ('$email', '$db_password', '$given_names', '$surname', '$date_of_birth', '$salt', 0, '$mechanic')");

            return true;
        }

        return false;
    }

    /**
     * @return User[]
     */
    public static function all()
    {
        $users = [];

        $query = self::$mysql->query("SELECT id FROM users");

        while ($row = $query->fetch_assoc()) {
            $users[] = new User($row['id']);
        }

        return $users;
    }

    public function __construct($id)
    {
        $query = self::$mysql->query("SELECT * FROM users WHERE id='$id'");

        if ($query == null || $query->num_rows != 1) {
            return false;
        }

        $results = $query->fetch_assoc();

        $this->given_names = $results['given_names'];
        $this->surname = $results['surname'];
        $this->id = $results['id'];
        $this->email = $results['email'];
        $this->date_of_birth = $results['date_of_birth'];
        $this->admin = $results['admin'];
        $this->password = $results['password'];
        $this->salt = $results['salt'];
        $this->mechanic = $results['mechanic'];

        return true;
    }

    public function getFullName()
    {
        return $this->surname . " " . $this->given_names;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function getGivenNames()
    {
        return $this->given_names;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getDOB()
    {
        return $this->date_of_birth;
    }

    public function isAdmin()
    {
        return $this->admin == 1;
    }

    public function isMechanic()
    {
        return $this->mechanic == 1;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Car[]
     */
    public function getCars()
    {
        $cars = [];

        foreach(Car::all() as $car){
            if($car->getOwner() == $this){
                $cars[] = $car;
            }
        }

        return $cars;
    }

    /**
     * @return User[]
     */
    public static function mechanics()
    {
        $mechanics = [];

        foreach(self::all() as $user){
            if($user->isMechanic()){
                $mechanics[] = $user;
            }
        }

        return $mechanics;
    }

    /**
     * @return Service[]
     */
    public function services()
    {
        if(!$this->isMechanic()){
            return false;
        }else{
            $services = [];

            foreach(Service::all() as $service){
                if($service->getFixer() == $this){
                    $services[] = $service;
                }
            }

            return $services;
        }
    }

    public function __toString(){
        return "$this->id";
    }
}