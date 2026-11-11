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

    public function getPagina(?int $idUsuario, int $pagina, int $limite = 10): array {
        $where = $idUsuario === null ? '' : ' WHERE p.Id_Usuario = :usuario';
        $count = $this->connection->prepare('SELECT COUNT(*) FROM Prestamo p' . $where);
        if ($idUsuario !== null) $count->bindValue(':usuario', $idUsuario, PDO::PARAM_INT);
        $count->execute();
        $total = (int)$count->fetchColumn();
        $paginas = max(1, (int)ceil($total / $limite));
        $pagina = min($pagina, $paginas);

        $statement = $this->connection->prepare("SELECT
            p.Id_Prestamo AS id,
            CONCAT(persona.Nombres_Persona, ' ', persona.Apellidos_Persona) AS usuario,
            p.emision_fecha_prestamo AS fecha,
            p.vencimiento_fecha_prestamo AS vencimiento,
            ep.nombre_estado AS estado, p.motivo_prestamo AS motivo
            FROM Prestamo p
            JOIN Estado_Prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo
            JOIN Usuario u ON u.Id_Usuario = p.Id_Usuario
            JOIN Persona persona ON persona.Id_Persona = u.Id_Persona"
            . $where . ' ORDER BY p.emision_fecha_prestamo DESC, p.Id_Prestamo DESC
            LIMIT :limite OFFSET :offset');
        if ($idUsuario !== null) $statement->bindValue(':usuario', $idUsuario, PDO::PARAM_INT);
        $statement->bindValue(':limite', $limite, PDO::PARAM_INT);
        $statement->bindValue(':offset', ($pagina - 1) * $limite, PDO::PARAM_INT);
        $statement->execute();
        return ['prestamos' => $statement->fetchAll(PDO::FETCH_ASSOC),
            'pagina' => $pagina, 'paginas' => $paginas, 'total' => $total];
    }

    function getById(int $id): array {
       $query = "SELECT
            p.Id_Prestamo AS id,
            p.emision_fecha_prestamo AS fecha,
            p.vencimiento_fecha_prestamo AS vencimiento,
            ep.nombre_estado AS estado,
            p.motivo_prestamo AS motivo
        FROM Prestamo p
        JOIN Estado_Prestamo ep
            ON ep.id_estado_prestamo = p.id_estado_prestamo
        WHERE p.Id_Usuario = :id_usuario
        ORDER BY p.emision_fecha_prestamo DESC, p.Id_Prestamo DESC";
        $statement = $this->connection->prepare($query);
        $statement->execute([':id_usuario' => $id]);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->response->success("Historial obtenido correctamente", $result, 1);
    }

}
