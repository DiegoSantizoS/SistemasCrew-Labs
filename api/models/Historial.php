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

    public function getPagina(?int $idUsuario, int $pagina, int $limite = 10, string $busqueda = ''): array
    {
        $origen = ' FROM Prestamo p
                    JOIN Estado_Prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo
                    JOIN Usuario u ON u.Id_Usuario = p.Id_Usuario
                    JOIN Persona persona ON persona.Id_Persona = u.Id_Persona';
        $condiciones = [];
        $parametros = [];
        if ($idUsuario !== null) {
            $condiciones[] = 'p.Id_Usuario = :usuario';
            $parametros['usuario'] = $idUsuario;
        }
        if ($busqueda !== '') {
            $condiciones[] = "(LOCATE(LOWER(:referencia), LOWER(CAST(p.Id_Prestamo AS CHAR))) > 0
                OR LOCATE(LOWER(:nombre), LOWER(CONCAT(persona.Nombres_Persona, ' ', persona.Apellidos_Persona))) > 0
                OR LOCATE(LOWER(:estado), LOWER(ep.nombre_estado)) > 0
                OR LOCATE(LOWER(:motivo), LOWER(p.motivo_prestamo)) > 0)";
            foreach (['referencia', 'nombre', 'estado', 'motivo'] as $campo) $parametros[$campo] = $busqueda;
        }
        $filtro = $condiciones ? ' WHERE ' . implode(' AND ', $condiciones) : '';
        $consultaTotal = $this->connection->prepare('SELECT COUNT(*)' . $origen . $filtro);
        foreach ($parametros as $campo => $valor) {
            $consultaTotal->bindValue(':' . $campo, $valor, $campo === 'usuario' ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $consultaTotal->execute();
        $total = (int)$consultaTotal->fetchColumn();
        $paginas = max(1, (int)ceil($total / $limite));
        $pagina = min(max(1, $pagina), $paginas);
        $consulta = $this->connection->prepare(
            "SELECT p.Id_Prestamo AS id,
                    CONCAT(persona.Nombres_Persona, ' ', persona.Apellidos_Persona) AS usuario,
                    p.emision_fecha_prestamo AS fecha, p.vencimiento_fecha_prestamo AS vencimiento,
                    ep.nombre_estado AS estado, p.motivo_prestamo AS motivo" . $origen . $filtro .
            ' ORDER BY p.emision_fecha_prestamo DESC, p.Id_Prestamo DESC LIMIT :limite OFFSET :offset'
        );
        foreach ($parametros as $campo => $valor) {
            $consulta->bindValue(':' . $campo, $valor, $campo === 'usuario' ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $consulta->bindValue(':limite', $limite, PDO::PARAM_INT);
        $consulta->bindValue(':offset', ($pagina - 1) * $limite, PDO::PARAM_INT);
        $consulta->execute();
        return ['prestamos' => $consulta->fetchAll(PDO::FETCH_ASSOC), 'pagina' => $pagina, 'paginas' => $paginas, 'total' => $total];
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
