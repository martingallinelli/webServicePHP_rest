<?php

require_once './config/Conection.php';
require_once './classes/errors/Answers.php';
require_once './classes/Log.php';

class Curso
{
    //! obtener cursos    
    /**
     * listaPost
     *
     * @param  mixed $pagina
     * @return array
     */
    public static function obtenerCursos()
    {
        //! conectar la bd
        $conn = Conection::conectar();
        //! consulta sql
        $sql = "SELECT * FROM cursos";
        //! guardar la consulta en memoria para ser analizada 
        $stmt = $conn->prepare($sql);
        //! ejecutar consulta
        if ($stmt->execute())
        {
            // traemos todos los cursos en un array asociativo
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
            Log::saveLog('a+', 'Se listaron todos los cursos correctamente! HTTP Status Code: 200 | Method: GET obtenerCursos');
            // devolver cursos
            return $cursos;
        } else {
            // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
            Log::saveLog('a+', 'Ocurrio un error! HTTP Status Code: 500 | Method: GET obtenerCursos');
            // error 500 interno del servidor
            return Answers::mensaje('500');
        }
    }

    //! obtener curso por id    
    /**
     * obtenerPost
     *
     * @param  mixed $id
     * @return array
     */
    public static function obtenerCurso($id)
    {
        //* si el id esta vacio en los datos de la url
        if($id == "")
        {
            // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
            Log::saveLog('a+', 'Ocurrio un error! HTTP Status Code: 400 | Method: GET obtenerCurso');
            // error 400 datos incompletos
            return Answers::mensaje('400');
        //* si el id esta ok en los datos de la url
        } else {
            //! conectar la bd
            $conn = Conection::conectar();
            //! consulta sql
            $sql = "SELECT * FROM cursos WHERE id = :id";
            //! guardar la consulta en memoria para ser analizada 
            $stmt = $conn->prepare($sql);
            //! bindear parametros
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            //! ejecutar consulta
            if ($stmt->execute())
            {
                // traer el curso en un array asociativo
                $curso = $stmt->fetch(PDO::FETCH_ASSOC);

                // si encontro el curso
                if ($curso)
                {
                    // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
                    Log::saveLog('a+', 'Se listo el curso ' . $id . '! HTTP Status Code: 200 | Method: GET obtenerCurso');
                    return $curso;
                // si no encontro el curso
                } else {
                    // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
                    Log::saveLog('a+', 'Ocurrio un error! HTTP Status Code: 404 | Method: GET obtenerCurso');
                    // devolver 404 no encontrado
                    return Answers::mensaje('404');
                }
            } else {
                // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
                Log::saveLog('a+', 'Ocurrio un error! HTTP Status Code: 500 | Method: GET obtenerCurso');
                // error 500 interno del servidor
                return Answers::mensaje('500');
            }
        }
    }

    //! capturar datos y crear curso    
    /**
     * capturarPost
     *
     * @param  mixed $array
     * @return array
     */
    public static function nuevoCurso($nombre)
    {
        //* si no existe alguno de los campos en los datos
        if($nombre == '')
        {
            // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
            Log::saveLog('a+', 'Ocurrio un error! HTTP Status Code: 400 | Method: POST nuevoCurso');
            // error 400 datos incompletos
             return Answers::mensaje('400');
        //* si existen los campos en los datos
        } else {
            //* guardar curso 
            $resp = self::insertarCurso($nombre);
            // si se guardo
            if($resp)
            {
                // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
                Log::saveLog('a+', 'Se guardo el nuevo curso! HTTP Status Code: 201 | Method: POST nuevoCurso');
                // devolver 201 elemento guardado
                return Answers::mensaje('201');
            // si no se guardo
            } else {
                // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
                Log::saveLog('a+', 'Ocurrio un error! HTTP Status Code: 500 | Method: POST nuevoCurso');
                // error 500 interno del servidor
                return Answers::mensaje('500');
            }
        }
    } 

    //! insertar curso    
    /**
     * insertarCurso
     *
     * @param  mixed $nombre
     * @return bool
     */
    private static function insertarCurso($nombre)
    {
        //! conectar la bd
        $conn = Conection::conectar();
        //! consulta sql
        $sql = "INSERT INTO cursos (nombre) VALUES (:nombre)";
        //! guardar la consulta en memoria para ser analizada 
        $stmt = $conn->prepare($sql);
        //! bindear parametros
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        //! ejecutar consulta
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    //! capturar datos y actualizar curso     
    /**
     * actualizarCurso
     *
     * @param  mixed $datos
     * @param  mixed $id
     * @return array
     */
    public static function actualizarCurso($datos, $id)
    {
        //* si el id recibido esta vacio
        if($id == '')
        {
            // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
            Log::saveLog('a+', 'Ocurrio un error! HTTP Status Code: 400 | Method: PUT actualizarCurso');
            // error 400 datos incompletos
            return Answers::mensaje('400');
        } else {
            // convertir de json a array
            $datos = json_decode($datos, true);
            // si el nombre esta vacio o no existe
            if (!isset($datos['nombre']) || empty($datos['nombre']))
            {
                // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
                Log::saveLog('a+', 'Ocurrio un error! HTTP Status Code: 400 | Method: PUT actualizarCurso');
                // error 400 datos incompletos
                return Answers::mensaje('400');
            } else {
                // guardar valores recibidos
                $nombre = $datos['nombre'];
                //* actualizar curso 
                $resp = self::modificarCurso($id, $nombre);
                // si se actualizo
                if($resp)
                {
                    // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
                    Log::saveLog('a+', 'Se actualizo el curso ' . $id . '! HTTP Status Code: 200 | Method: PUT actualizarCurso');
                    // devolver 200 curso actualizado
                    return Answers::mensaje('200', 'Curso actualizado');
                // si no se guardo
                } else {
                    // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
                    Log::saveLog('a+', 'Ocurrio un error! HTTP Status Code: 404 | Method: PUT actualizarCurso');
                    // error 404 recurso no encontrado
                    return Answers::mensaje('404');
                }
            }
        }

    }

    //! actualizar curso    
    /**
     * modificarCurso
     *
     * @param  mixed $id
     * @param  mixed $nombre
     * @return bool
     */
    private static function modificarCurso($id, $nombre)
    {
        //! conectar la bd
        $conn = Conection::conectar();
        //! consulta sql
        $sql = "UPDATE cursos SET nombre = :nombre WHERE id = :id";
        //! guardar la consulta en memoria para ser analizada 
        $stmt = $conn->prepare($sql);
        //! bindear parametros
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        //! ejecutar consulta
        if ($stmt->execute()) {
            // numero de filas afectadas
            $row = $stmt->rowCount();
            return $row;
        } else {
            // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
            Log::saveLog('a+', 'Ocurrio un error! HTTP Status Code: 500 | Method: PUT actualizarCurso');
            // error 500 interno del servidor
            return Answers::mensaje('500');
        }
    }

    //! capturar datos y eliminar post    
    /**
     * eliminarCurso
     *
     * @param  mixed $id
     * @return array
     */
    public static function eliminarCurso($id)
    {
        //* si el id recibido esta vacio
        if($id == '')
        {
            // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
            Log::saveLog('a+', 'Ocurrio un error! HTTP Status Code: 400 | Method: DELETE eliminarCurso');
            // error 400 datos incompletos
            return Answers::mensaje('400');
        } else {
            //* eliminar curso 
            $resp = self::borrarCurso($id);
            // si se elimino
            if($resp)
            {
                // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
                Log::saveLog('a+', 'Se elimino el curso ' . $id . '! HTTP Status Code: 200 | Method: DELETE eliminarCurso');
                // devolver 200 curso eliminado
                return Answers::mensaje('200', 'Curso eliminado!');
            // si no se guardo
            } else {
                // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
                Log::saveLog('a+', 'Ocurrio un error! HTTP Status Code: 404 | Method: DELETE eliminarCurso');
                // error 404 recurso no encontrado
                return Answers::mensaje('404');
            }
        }
    }  

    //! eliminar post    
    /**
     * borrarCurso
     *
     * @param  mixed $id
     * @return bool
     */
    private static function borrarCurso($id)
    {
        //! conectar la bd
        $conn = Conection::conectar();
        //! consulta sql
        $sql = "DELETE FROM cursos WHERE id = :id";
        //! guardar la consulta en memoria para ser analizada 
        $stmt = $conn->prepare($sql);
        //! bindear parametros
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        //! ejecutar consulta
        if ($stmt->execute()) {
            // numero de filas afectadas
            $row = $stmt->rowCount();
            return $row;
        } else {
            // guardar log (a+, seguir escribiendo sin sobreescribir lo existente)
            Log::saveLog('a+', 'Ocurrio un error! HTTP Status Code: 500 | Method: DELETE eliminarCurso');
            // error 500 interno del servidor
            return Answers::mensaje('500');
        }
    }
}