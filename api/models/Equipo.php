<?php

class Equipo
{
    private connection $connection;
    private response $response;

    public function __construct(Connection $connection, Response $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }
    //Función para obtener todos los equipos
    function getPreview($ultimo = 0, $busqueda = '')
    {
        $query = "SELECT id, Codigo, Descripcion, Categoria, Estado FROM vwEquipo WHERE id > :ultimo";
        $parametros = ['ultimo' => $ultimo];
        if ($busqueda !== '') {
            $query .= " AND (LOCATE(LOWER(:codigo), LOWER(Codigo)) > 0
                         OR LOCATE(LOWER(:descripcion), LOWER(Descripcion)) > 0)";
            $parametros['codigo'] = $busqueda;
            $parametros['descripcion'] = $busqueda;
        }
        $query .= " ORDER BY id ASC LIMIT 11";
        $statement = $this->connection->prepare($query);
        $statement->execute($parametros);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $hasMore = count($result) > 10;
        $result = array_slice($result, 0, 10);
        $nextCursor = $result ? (int)$result[count($result) - 1]['id'] : null;
        foreach ($result as &$row) unset($row['id']);
        unset($row);
        $this->response->success("Equipos obtenidos correctamente", $result, 1, [
            'has_more' => $hasMore,
            'next_cursor' => $nextCursor,
            'paginacion' => ['hayMas' => $hasMore, 'siguiente' => $nextCursor ?? 0]
        ]);
        /*$registers = "SELECT COUNT(*) as registros FROM vwEquipo";*/
        /*$statement2 = $this->connection->prepare($registers);
        $statement2->execute();
        $result2 = $statement2->fetchAll(PDO::FETCH_ASSOC);*/
      
    }
    function getAll($codigo){
          $query = "SELECT * FROM vwEquipo where codigo = :codigo";
        $statement = $this->connection->prepare($query);
        $statement->execute([
            'codigo' => $codigo
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (empty($result)) {
            $this->response->error(
                "No se encontró el equipo solicitado",
                2005,
                404
            );
            return;
        }
        $this->response->success("Detalle de equipo obtenido correctamente", $result, 1);
        
    }

    //Función para añadir un nuevo equipo
    function add($arrayData)
    {
        $Codigo = $arrayData['codigo'];
        $Categoria = $arrayData['categoria'];
        $Ubicacion = $arrayData['ubicacion'];
        $Estado = $arrayData['estado'];
        $Marca = $arrayData['marca'] ?? null;
        $Modelo = $arrayData['modelo'] ?? null;
        $Serie = $arrayData['serie'] ?? null;
        $Descripcion = $arrayData['descripcion'];
        $Imagen = $arrayData['imagen'] ?? null;

        $query = "INSERT INTO Equipo (Codigo_Equipo, Id_Categoria, Id_Ubicacion, 
        Id_Estado_Equipo, Marca_Equipo, Modelo_Equipo, Serie_Equipo, Descripcion_Equipo, 
        Imagen_Equipo) 
        VALUES (:codigo, :categoria, :ubicacion, :estado, :marca, :modelo, :serie, :descripcion, :imagen)";
        $Statement = $this->connection->prepare($query);
        $Statement->execute([
            'codigo' => $Codigo,
            'categoria' => $Categoria,
            'ubicacion' => $Ubicacion,
            'estado' => $Estado,
            'marca' => $Marca,
            'modelo' => $Modelo,
            'serie' => $Serie,
            'descripcion' => $Descripcion,
            'imagen' => $Imagen
        ]);
        $this->response->success("Equipo agregado correctamente", [], 1);
    }

    //Función para actualizar un equipo
    function update($arrayData)
    {
        $Id_Equipo = $arrayData['id_equipo'];
        $Codigo = $arrayData['codigo'];
        $Categoria = $arrayData['categoria'];
        $Ubicacion = $arrayData['ubicacion'];
        $Estado = $arrayData['estado'];
        $Marca = $arrayData['marca'] ?? null;
        $Modelo = $arrayData['modelo'] ?? null;
        $Serie = $arrayData['serie'] ?? null;
        $Descripcion = $arrayData['descripcion'];
        $Imagen = $arrayData['imagen'] ?? null;

        $query = "UPDATE Equipo SET Codigo_Equipo=:codigo, Id_Categoria=:categoria, Id_Ubicacion=:ubicacion, 
        Id_Estado_Equipo=:estado, Marca_Equipo=:marca, Modelo_Equipo=:modelo, Serie_Equipo=:serie, Descripcion_Equipo=:descripcion, 
        Imagen_Equipo=:imagen WHERE Id_Equipo=:id_equipo";
        $Statement = $this->connection->prepare($query);
        $Statement->execute([
            'id_equipo' => $Id_Equipo,
            'codigo' => $Codigo,
            'categoria' => $Categoria,
            'ubicacion' => $Ubicacion,
            'estado' => $Estado,
            'marca' => $Marca,
            'modelo' => $Modelo,
            'serie' => $Serie,
            'descripcion' => $Descripcion,
            'imagen' => $Imagen
        ]);
        if ($Statement->rowCount() > 0) {
            $this->response->success("Equipo actualizado correctamente", [], 1);
        } else {
            throw new Exception("No se pudo actualizar el equipo");
        }
    }

    //Función para hacer soft delete de un equipo, cambiando su estado a 4 (eliminado)
    function delete($arrayData)
    {
        $Codigo_Equipo = $arrayData['Codigo'];

        $query = "UPDATE Equipo 
              SET Id_Estado_Equipo = 4
              WHERE Codigo_Equipo = :Codigo_Equipo";

        $Statement = $this->connection->prepare($query);

        $Statement->execute([
            'Codigo_Equipo' => $Codigo_Equipo
        ]);

        if ($Statement->rowCount() > 0) {
            $this->response->success(
                "Equipo eliminado correctamente",
                [],
                1
            );
        } else {
            throw new Exception("No se pudo eliminar el equipo");
        }
    }

}
