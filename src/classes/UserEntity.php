<?php

class UserEntity
{
    private $id = 0;
    private $full_name;
    private $phone_number;
    private $birthday = null;
    private $address = null;
   
    public function __construct(array $data)
	{
        if(isset($data['id']))
		{
            $this->id = $data['id'];
        }
        $this->full_name = $data['full_name'];
        $this->phone_number = $data['phone_number'];
		if($data['birthday'] != "")
		{
			$this->birthday = $data['birthday'];
		}
		if($data['address'] != "")
		{
			$this->address = $data['address'];
		}
    }
    public function getId()
	{
        return $this->id;
    }
    public function getFullName()
	{
        return $this->full_name;
    }
    public function getPhoneNumber()
	{
        return $this->phone_number;
    }
    public function getBirthday()
	{
        return $this->birthday;
    }
	public function getAddress()
	{
        return $this->address;
    }
}