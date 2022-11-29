<?php

declare(strict_types=1);

namespace App\Controller;
use App\Model\Curso;
use App\Repository\CategoriaRepository;
use App\Repository\CursoRepository;
use Dompdf\Dompdf;
use Exception;


class CursoController extends AbstractController
{
    private CursoRepository $repository;

    public function __construct()
    {
        $this->repository = new CursoRepository;
    }



    public function listar() : void
    {
        $cursos = $this->repository->buscarTodos();

        $this->render('curso/listar', [
            'cursos' => $cursos,
        ]);
    }

    public function cadastrar(): void
    {
        if(true === empty($_POST)){
            $this->categoriaRepository = new CategoriaRepository;
            $this->render('curso/cadastrar', [
                'categorias' => $this->categoriaRepository->buscarTodos()
        ]);
            return;
        }
        
        $curso = new Curso();
        $curso->nome = $_POST['nome'];
        $curso->cargaHoraria = $_POST['cargaHoraria'];
        $curso->descricao = $_POST['descricao'];
        $curso->categoria_id = intval($_POST['categoria']);
        
        try{
            $this->repository->inserir($curso);
        } catch(Exception $exception){
            if(str_contains($exception->getMessage(), 'nome')){
                die('O curso já existe');
            }

            die('Vish, aconteceu um erro');
        }
        $this->redirect('/cursos/listar');      
    }

    public function excluir() : void
    {
        $id = $_GET['id'];
        $this->repository->excluir($id);
        
        $this->redirect('/cursos/listar');
    }

    public function editar() : void
    {
        $id = $_GET['id'];
        $curso = $this->repository->buscarUm($id);
        $this->categoriaRepository = new CategoriaRepository;
        $this->render('curso/editar', [
            $curso,
            'categorias' => $this->categoriaRepository->buscarTodos()
        ]); 
        if(false === empty($_POST)){

            $curso->nome = $_POST['nome'];
            $curso->cargaHoraria = $_POST['cargaHoraria'];
            $curso->descricao = $_POST['descricao'];
            $curso->categoria_id = intval($_POST['categoria']);

            try{
                $this->repository->atualizar($curso, $id);
            } catch(Exception $exception){
                if(str_contains($exception->getMessage(), 'nome')){
                    die('O curso já existe');
                }

                die('Vish, aconteceu um erro');
            }
            $this->redirect('/cursos/listar');
        }
    }

    public function relatorio(): void
    {
        date_default_timezone_set('America/Sao_Paulo');
        $hoje = date('d/m/Y');
        $cursos = $this->repository->buscarTodos();
        $this->categoriaRepository = new CategoriaRepository;
        $categorias = $this->categoriaRepository->buscarTodos();
        $corpotabela = '';
        foreach($cursos as $cadaCurso){
            foreach($categorias as $cadaCategoria){
                if($cadaCurso[5] == $cadaCategoria->id){
                    $colunaCategoria = $cadaCategoria->nome;
                }
                if ($cadaCurso[4] == 1) {
                    $cadaCurso[4] = "Ativo";
                } else {
                    $cadaCurso[4] = "Desativado";
                }
            }
            $corpotabela .= "
            <tr>
                <td>{$cadaCurso[0]}</td>
                <td>{$cadaCurso[1]}</td>
                <td>{$cadaCurso[2]}</td>
                <td>{$cadaCurso[3]}</td>
                <td>{$cadaCurso[4]}</td>
                <td>{$colunaCategoria}</td>
            </tr> ";
        } 

        $design =  "
            <h1>Relatorio de Cursos</h1>
            <hr>
            <em>Gerado em {$hoje}</em>
            <br>
            <table border='1' width='100%' style='margin-top: 30px; text-align:center;'>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Carga Horaria</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th>Categoria</th>
                    </tr>
                </thead>
                <tbody>" 
                . 
                    $corpotabela 
                . 
                "</tbody>
            </table>
        ";
        $dompdf = new Dompdf();
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($design);
        $dompdf->render();
        $dompdf->stream('relatorio-de-cursos.pdf', ['Attachment' => 0]);
    }
}