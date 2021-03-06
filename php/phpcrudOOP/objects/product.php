<?php
    class Product {

        private $conn;
        private $table_name = "products";

        public $id;
        public $name;
        public $price;
        public $description;
        public $category_id;
        public $image;
        public $timestamp;

        public function __construct($db)
        {
            $this->conn = $db;
        }

        function create() {

            $query = "INSERT INTO
                        " . $this->table_name . "
                    SET
                        name=:name, price=:price, description=:description, category_id=:category_id, image=:image, created=:created";

            $stmt = $this->conn->prepare($query);

            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->price = htmlspecialchars(strip_tags($this->price));
            $this->description = htmlspecialchars(strip_tags($this->description));
            $this->category_id = htmlspecialchars(strip_tags($this->category_id));
            $this->image = htmlspecialchars(strip_tags($this->image));

            $this->timestamp = date('Y-m-d H:i:s');

            $stmt->bindParam(":name", $this->name);
            $stmt->bindParam(":price", $this->price);
            $stmt->bindParam(":description", $this->description);
            $stmt->bindParam(":category_id", $this->category_id);
            $stmt->bindParam(":image", $this->image);
            $stmt->bindParam(":created", $this->timestamp);

            if($stmt->execute()){
                return true;
            } else {
                return false;
            }
        }

        function uploadPhoto() {
            $result_message = "";

            if($this->image) {

                $target_directory = "uploads/";
                $target_file = $target_directory . $this->image;
                $file_type = pathinfo($target_file, PATHINFO_EXTENSION);

                $file_upload_error_messages = "";

                $check = getimagesize($_FILES["image"]["tmp_name"]);
                if($check!==false){
                    // submitted file is an image
                }else{
                    $file_upload_error_messages.="<div>Submitted file is not an image.</div>";
                }
                
                // make sure certain file types are allowed
                $allowed_file_types=array("jpg", "jpeg", "png", "gif");
                if(!in_array($file_type, $allowed_file_types)){
                    $file_upload_error_messages.="<div>Only JPG, JPEG, PNG, GIF files are allowed.</div>";
                }
                
                // make sure file does not exist
                if(file_exists($target_file)){
                    $file_upload_error_messages.="<div>Image already exists. Try to change file name.</div>";
                }
                
                // make sure submitted file is not too large, can't be larger than 1 MB
                if($_FILES['image']['size'] > (1024000)){
                    $file_upload_error_messages.="<div>Image must be less than 1 MB in size.</div>";
                }
                
                // make sure the 'uploads' folder exists
                // if not, create it
                if(!is_dir($target_directory)){
                    mkdir($target_directory, 0777, true);
                }


                // if $file_upload_error_messages is still empty
                if(empty($file_upload_error_messages)){
                    // it means there are no errors, so try to upload the file
                    if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)){
                        // it means photo was uploaded
                    }else{
                        $result_message.="<div class='alert alert-danger'>";
                            $result_message.="<div>Unable to upload photo.</div>";
                            $result_message.="<div>Update the record to upload photo.</div>";
                        $result_message.="</div>";
                    }
                }
 
                // if $file_upload_error_messages is NOT empty
                else{
                    // it means there are some errors, so show them to user
                    $result_message.="<div class='alert alert-danger'>";
                        $result_message.="{$file_upload_error_messages}";
                        $result_message.="<div>Update the record to upload photo.</div>";
                    $result_message.="</div>";
                }
            }

            return $result_message;
        }

        function readOne() {
            $query = "SELECT name, price, description, category_id, image 
                    FROM " . $this->table_name . "
                    WHERE id = ?
                    LIMIT 0,1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->name = $row['name'];
            $this->price = $row['price'];
            $this->description = $row['description'];
            $this->category_id = $row['category_id'];
            $this->image = $row['image'];
        }

        // function readOne(){
 
        //     $query = "SELECT
        //                 name, price, description, category_id
        //             FROM
        //                 " . $this->table_name . "
        //             WHERE
        //                 id = ?
        //             LIMIT
        //                 0,1";
         
        //     $stmt = $this->conn->prepare( $query );
        //     $stmt->bindParam(1, $this->id);
        //     $stmt->execute();
         
        //     $row = $stmt->fetch(PDO::FETCH_ASSOC);
         
        //     $this->name = $row['name'];
        //     $this->price = $row['price'];
        //     $this->description = $row['description'];
        //     $this->category_id = $row['category_id'];
        // }

        function readAll($from_record_num, $records_per_page) {

            $query = "SELECT id, name, description, price, category_id
                    FROM ". $this->table_name . "
                    ORDER BY name ASC
                    LIMIT {$from_record_num}, {$records_per_page}";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt;
        }

        function update() {
            $query = "UPDATE 
                    " . $this->table_name . "
                    SET
                        name = :name,
                        price = :price,
                        description = :description,
                        category_id = :category_id
                    WHERE 
                        id = :id";

            $stmt = $this->conn->prepare($query);

            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->price = htmlspecialchars(strip_tags($this->price));
            $this->category_id=htmlspecialchars(strip_tags($this->category_id));
            $this->id=htmlspecialchars(strip_tags($this->id));

            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':price', $this->price);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':category_id', $this->category_id);
            $stmt->bindParam(':id', $this->id);

            if($stmt->execute()){
                return true;
            } else {
                return false;
            }
        }

        function delete(){
 
            $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
             
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
         
            if($result = $stmt->execute()){
                return true;
            }else{
                return false;
            }
        }

        public function countAll() {

            $query = "SELECT id FROM " . $this->table_name . "";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $num = $stmt->rowCount();

            return $num;
        }


        public function search($search_term, $from_record_num, $records_per_page) {
            
            $query = "SELECT
                        c.name AS category_name, p.id, p.name, p.description, p.price, p.category_id, p.created
                    FROM
                        " . $this->table_name . " p
                        LEFT JOIN
                            categories c
                                ON p.category_id = c.id
                    WHERE
                        p.name LIKE ? OR p.description LIKE ?
                    ORDER BY
                        p.name ASC
                    LIMIT
                        ?, ?";
            
            $stmt = $this->conn->prepare($query);

            $search_term = "%{$search_term}%";
            $stmt->bindParam(1, $search_term);
            $stmt->bindParam(2, $search_term);
            $stmt->bindParam(3, $from_record_num, PDO::PARAM_INT);
            $stmt->bindParam(4, $records_per_page, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt;
        }


        public function countAll_BySearch($search_term) {
            $query = "SELECT
                        COUNT(*) AS total_rows
                    FROM
                        " . $this->table_name . " p
                        LEFT JOIN
                            categories c
                                ON p.category_id = c.id
                    WHERE
                        p.name LIKE ?";

            $stmt = $this->conn->prepare($query);
            
            $search_term = "%{$search_term}%";
            $stmt->bindParam(1, $search_term);
            
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row['total_rows'];
        }
    }
?>