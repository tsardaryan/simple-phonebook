<?php
class UserMapper extends Mapper
{
    public function users() {
        $sql = "
			SELECT
				id,
				full_name,
				phone_number,
				birthday,
				address
            FROM
				users
			";
        $stmt = $this->db->query($sql);
        $result = [];
        while($row = $stmt->fetch())
		{
            $result[] = new UserEntity($row);
        }
        return $result;
    }
	
	public function search($keyword) {
        $sql = "
			SELECT
				id,
				full_name,
				phone_number,
				birthday,
				address
            FROM
				users
			WHERE
				full_name LIKE :keyword
				OR phone_number LIKE :keyword
			";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["keyword" => "%".$keyword."%"]);
		$return = [];
        while($row = $stmt->fetch())
		{
            $return[] = new UserEntity($row);
        }
		
        return $return;
    }

    public function user($id_user) {
        $sql = "
			SELECT
				id,
				full_name,
				phone_number,
				birthday,
				address
            FROM
				users
			WHERE
				id = :id_user
			";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["id_user" => $id_user]);
        if($result)
		{
            return new UserEntity($stmt->fetch());
        }
    }
	
	public function isPhoneNumberExists($id_user, $phone_number)
	{
		$sql = "
			SELECT
				count(*) count
            FROM
				users
			WHERE
				id != :id_user
				AND phone_number = :phone_number
			";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["id_user" => $id_user, "phone_number" => $phone_number]);
        if($result)
		{
            return ($stmt->fetchColumn() > 0);
        }
	}
	
    public function add(UserEntity $user) {
        $sql = "
			INSERT INTO
				users (full_name, phone_number, birthday, address)
				values (:full_name, :phone_number, :birthday, :address)
            ";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "full_name" => $user->getFullName(),
            "phone_number" => $user->getPhoneNumber(),
            "birthday" => $user->getBirthday(),
            "address" => $user->getAddress(),
        ]);
        if(!$result)
		{
            throw new Exception("could not add user");
        }
		return $this->db->lastInsertId();
    }
	
	public function update($id_user, UserEntity $user) {
        $sql = "
			UPDATE
				users
			SET
				full_name = :full_name,
				phone_number = :phone_number,
				birthday = :birthday,
				address = :address
			WHERE
				id = :id_user
            ";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "id_user" => $id_user,
            "full_name" => $user->getFullName(),
            "phone_number" => $user->getPhoneNumber(),
            "birthday" => $user->getBirthday(),
            "address" => $user->getAddress(),
        ]);
        if(!$result)
		{
            throw new Exception("could not update user");
        }
    }
	
	public function remove($id_user) {
        $sql = "
			DELETE
            FROM
				users
			WHERE
				id = :id_user
			";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["id_user" => $id_user]);
        if(!$result)
		{
            throw new Exception("could not remove user");
        }
    }
}