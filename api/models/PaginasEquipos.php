<?php

class PaginasEquipos{
    private Connection $connection;
    private Response $response;
    public function __construct(Connection $con, Response $res)
    {
        $this->connection = $con;
        $this->response = $res;
    }
    function getall(){
        $query = "SELECT * from IndiceEquipos;";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $query2 = "SELECT COUNT(*) AS paginas from IndiceEquipos;";
        $statement2 = $this->connection->prepare($query2);
        $statement2->execute();
        $result2 = $statement2->fetchAll(PDO::FETCH_ASSOC);
        $this->response->success("Indices obtenidos correctamente", $result, 1, $result2);
        }

}