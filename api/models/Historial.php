<?php

Class Historial
{
    private connection $connection;
    private response $response;

    public function __construct(Connection $connection, Response $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }

    function getAll(){
    
        $query = "SELECT * FROM vwHistorial";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (empty($result)) {
            $this->response->error(
                "No se encontró el historial solicitado",
                -404,
                404
            );
        }
        $this->response->success("Historial obtenido correctamente", $result, 1);
    }
    function getById($id_usuario){
        $query = "SELECT * FROM vwHistorial WHERE Usuario = :id_usuario";
        $statement = $this->connection->prepare($query);
        $statement->execute([
            'id_usuario' => $id_usuario
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (empty($result)) {
            $this->response->error(
                "No se encontró el historial solicitado",
                -404,
                404
            );
        }
        $this->response->success("Historial obtenido correctamente", $result, 1);
    }

}