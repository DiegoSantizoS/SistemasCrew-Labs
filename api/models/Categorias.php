<?php

class Categorias
{
    private $connection;
    private $response;

    public function __construct($connection, $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }

    // Función para obtener todas las categorías
    function getAll()
    {
        $query = "SELECT * FROM Categoria WHERE Activo = 1";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->response->success("Categorías obtenidas correctamente", $result, 1);
    }
    function add($arrayData)
    {
        $Categoria = $arrayData['categoria'];
        $Descripcion = $arrayData['descripcion'];
        $Activo = $arrayData['activo'];

        $query = "INSERT INTO Categoria (Nombre_Categoria, Descripcion_Categoria, Activo) VALUES (:categoria, :descripcion, :activo)";
        $Statement = $this->connection->prepare($query);
        $Statement->execute([
            'categoria' => $Categoria,
            'descripcion' => $Descripcion,
            'activo' => $Activo
        ]);
        $this->response->success("Categoría agregada correctamente", [], 1);
    }
    function update($arrayData)
    {
        $Id_Categoria = $arrayData['id_categoria'];
        $Categoria = $arrayData['categoria'];
        $Descripcion = $arrayData['descripcion'];
        $Activo = $arrayData['activo'];

        $query = "UPDATE Categoria SET Nombre_Categoria = :categoria, Descripcion_Categoria = :descripcion, Activo = :activo WHERE Id_Categoria = :id_categoria";
        $Statement = $this->connection->prepare($query);
        $Statement->execute([
            'id_categoria' => $Id_Categoria,
            'categoria' => $Categoria,
            'descripcion' => $Descripcion,
            'activo' => $Activo
        ]);
        $this->response->success("Categoría actualizada correctamente", [], 1);
    }
    function delete($arrayData)
    {
        $Id_Categoria = $arrayData['id_categoria'];

        $query = "UPDATE Categoria SET Activo = 0 WHERE Id_Categoria = :id_categoria";
        $Statement = $this->connection->prepare($query);
        $Statement->execute([
            'id_categoria' => $Id_Categoria
        ]);
        $this->response->success("Categoría eliminada correctamente", [], 1);
    }
}
