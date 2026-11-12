<?php

require_once __DIR__ . '/Categorias.php';

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
        $Categoria = $arrayData['categoria'];
        $consultaCategoria = $this->connection->prepare(
            "SELECT Nombre_Categoria FROM Categoria WHERE Id_Categoria = :categoria AND Activo = 1"
        );
        $consultaCategoria->execute(['categoria' => $Categoria]);
        $nombreCategoria = $consultaCategoria->fetchColumn();
        if ($nombreCategoria === false) {
            $this->response->error("Selecciona una categoría activa.", 2002, 422);
        }

        $prefijo = Categorias::prefijoCodigoEquipo($nombreCategoria);
        if ($prefijo === null) {
            $this->response->error("El nombre de la categoría debe contener letras para generar el código.", 2002, 422);
        }

        $Codigo = $prefijo . trim($arrayData['sufijo']);
        if (mb_strlen($Codigo, 'UTF-8') > 50) {
            $this->response->error("El código completo del equipo admite hasta 50 caracteres.", 2002, 422);
        }

        $Ubicacion = $arrayData['ubicacion'];
        $Estado = $arrayData['estado'];
        $Marca = $arrayData['marca'] ?? null;
        $Modelo = $arrayData['modelo'] ?? null;
        $Serie = trim($arrayData['serie'] ?? '');
        $Serie = $Serie === '' ? null : $Serie;
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
        $this->connection->beginTransaction();
        try {
            $this->bloquearEquipoEditable('Id_Equipo', $arrayData['id_equipo']);
            $estadoConsulta = $this->connection->prepare('SELECT Nombre_Estado_Equipo FROM Estado_Equipo WHERE id_estado_Equipo = ?');
            $estadoConsulta->execute([$arrayData['estado']]);
            $nombreEstado = $estadoConsulta->fetchColumn();
            if ($nombreEstado === false) throw new DomainException('Selecciona un estado de equipo existente.');
            if ($nombreEstado === 'Prestado') throw new DomainException('El estado Prestado solo se asigna al registrar un prestamo.');
            $Id_Equipo = $arrayData['id_equipo'];
            $Codigo = $arrayData['codigo'];
            $Categoria = $arrayData['categoria'];
            $Ubicacion = $arrayData['ubicacion'];
            $Estado = $arrayData['estado'];
            $Marca = $arrayData['marca'] ?? null;
            $Modelo = $arrayData['modelo'] ?? null;
            $Serie = trim($arrayData['serie'] ?? '');
            $Serie = $Serie === '' ? null : $Serie;
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
            $this->connection->commit();
        } catch (\Throwable $error) {
            if ($this->connection->inTransaction()) $this->connection->rollBack();
            throw $error;
        }
        $this->response->success("Equipo actualizado correctamente", [], 1);
    }

    //Función para hacer soft delete de un equipo, cambiando su estado a 4 (eliminado)
    function delete($arrayData)
    {
        $this->connection->beginTransaction();
        try {
            $this->bloquearEquipoEditable('Codigo_Equipo', $arrayData['Codigo']);
            $Codigo_Equipo = $arrayData['Codigo'];

            $query = "UPDATE Equipo
                  SET Id_Estado_Equipo = 4
                  WHERE Codigo_Equipo = :Codigo_Equipo";

            $Statement = $this->connection->prepare($query);

            $Statement->execute([
                'Codigo_Equipo' => $Codigo_Equipo
            ]);

            $this->connection->commit();
        } catch (\Throwable $error) {
            if ($this->connection->inTransaction()) $this->connection->rollBack();
            throw $error;
        }
            $this->response->success(
                "Equipo eliminado correctamente",
                [],
                1
            );
    }

    // El bloqueo se conserva hasta confirmar la edicion o baja del equipo.
    private function bloquearEquipoEditable(string $campo, $valor): void
    {
        if (!in_array($campo, ['Id_Equipo', 'Codigo_Equipo'], true)) {
            throw new InvalidArgumentException('Campo de equipo no valido.');
        }
        $consulta = $this->connection->prepare("SELECT Id_Equipo FROM Equipo WHERE $campo = ? FOR UPDATE");
        $consulta->execute([$valor]);
        $id = $consulta->fetchColumn();
        if ($id === false) throw new OutOfBoundsException('Equipo no encontrado.');

        // Lectura actual con bloqueo: incluye solicitudes pendientes y prestamos vencidos.
        $consulta = $this->connection->prepare("SELECT d.Id_Detalle
            FROM Detalle_Prestamo d
            JOIN Prestamo p ON p.Id_Prestamo = d.Id_Prestamo
            JOIN Estado_Prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo
            WHERE d.Id_Equipo = ? AND d.Fecha_Devolucion IS NULL
              AND ep.nombre_estado IN ('Pendiente', 'Activo', 'Vencido')
            LIMIT 1 FOR UPDATE");
        $consulta->execute([$id]);
        if ($consulta->fetchColumn() !== false) {
            throw new DomainException('No puedes editar ni dar de baja este equipo porque tiene un prestamo pendiente de devolucion. Resuelve el prestamo primero.');
        }
    }

}
