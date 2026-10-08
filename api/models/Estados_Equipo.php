<?php

Class Estados_Equipo{
    private Connection $connection;
    private Response $response;
    public function __construct(Connection $con, Response $res){
        $this->connection = $con;
        $this->response = $res;
    }
    function getAll(){
        $query = "SELECT * FROM Estado_Equipo;";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->response->success("Estados obtenidos correctamente", $result,1);
    }
        
}