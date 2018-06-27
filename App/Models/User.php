<?php

namespace App\Models;

use App\Base;

class User extends Base
{
    private $id;
    private $email;
    private $password;
    private $salt;

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
     * @return bool
     */
    public static function create($email, $password)
    {
        $email = self::$mysql->real_escape_string($email);

        $query = self::$mysql->query("SELECT email FROM users WHERE email='$email'");

        if ($query != null && $query->num_rows != 0) {
            return false;
        }

        if ($query->num_rows == 0) {
            $salt = self::generateSalt();
            $db_password = sha1($password . "loller" . $salt);


            self::$mysql->query("INSERT INTO users (email, password, salt) VALUES ('$email', '$db_password', '$salt')");

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

        $this->id = $results['id'];
        $this->email = $results['email'];
        $this->password = $results['password'];
        $this->salt = $results['salt'];

        return true;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getId()
    {
        return $this->id;
    }

    public function __toString(){
        return "$this->id";
    }
}