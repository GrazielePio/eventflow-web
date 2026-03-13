<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controller de Eventos
 * Gerencia todas as operações relacionadas aos eventos
 */
class EventoController extends Controller
{
    /**
     * Lista todos os eventos (público)
     */
    public function index(Request $request)
    {
        $eventos = $request->user()->eventos()->orderBy('data', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $eventos,
            'message' => 'Eventos carregados com sucesso'
        ], 200);
    }

    /**
     * Exibe um evento específico
     */
    public function show($id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'success' => false,
                'message' => 'Evento não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $evento
        ], 200);
    }

    /**
     * Cria um novo evento (protegido por autenticação)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'data' => 'required|date',
            'local' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ], [
            'titulo.required' => 'O campo título é obrigatório.',
            'data.required' => 'O campo data é obrigatório.',
            'data.date' => 'Por favor, insira uma data válida.',
            'local.required' => 'O campo local é obrigatório.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Cria evento associando ao usuário autenticado
        $evento = Evento::create([
            'user_id' => $request->user()->id,
            'titulo' => $request->titulo,
            'data' => $request->data,
            'local' => $request->local,
            'descricao' => $request->descricao,
        ]);

        return response()->json([
            'success' => true,
            'data' => $evento,
            'message' => 'Evento criado com sucesso!'
        ], 201);
    }

    /**
     * Atualiza um evento existente
     */
    public function update(Request $request, $id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'success' => false,
                'message' => 'Evento não encontrado'
            ], 404);
        }

        // Verifica se o usuário é o dono do evento
        if ($evento->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para editar este evento'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'data' => 'required|date',
            'local' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ], [
            'titulo.required' => 'O campo título é obrigatório.',
            'data.required' => 'O campo data é obrigatório.',
            'data.date' => 'Por favor, insira uma data válida.',
            'local.required' => 'O campo local é obrigatório.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $evento->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $evento,
            'message' => 'Evento atualizado com sucesso!'
        ], 200);
    }

    /**
     * Exclui um evento
     */
    public function destroy(Request $request, $id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'success' => false,
                'message' => 'Evento não encontrado'
            ], 404);
        }

        // Verifica se o usuário é o dono do evento
        if ($evento->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para excluir este evento'
            ], 403);
        }

        $evento->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evento excluído com sucesso!'
        ], 200);
    }
}