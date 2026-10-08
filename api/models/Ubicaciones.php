<?php

class Ubicaciones{
    private Connection $connection;
    private Response $response;
    public function __construct(Connection $con, Response $res){
        $this->connection = $con;
        $this->response = $res;
    }
    function getAll(){
        $query = "SELECT * FROM Ubicacion;";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->response->success("Ubicaciones obtenidas correctamente", $result,1);
    }
        
}