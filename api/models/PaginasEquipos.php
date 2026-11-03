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
        $query = "SELECT id_Pagina, Ultimo_id_pagina
                  FROM IndiceEquipos
                  WHERE Ultimo_id_pagina = 0
                     OR Ultimo_id_pagina < (SELECT MAX(id) FROM vwEquipo)
                  ORDER BY id_Pagina";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $result2 = [['paginas' => count($result)]];
        $this->response->success("Indices obtenidos correctamente", $result, 1, $result2);
        }
    
}
