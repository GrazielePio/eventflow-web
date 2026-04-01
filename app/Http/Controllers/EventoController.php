<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class EventoController extends Controller
{
    /**
     * Lista os eventos do usuário logado
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
     * Cria um novo evento com Foto e PDF
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo'      => 'required|string|max:255',
            'data'        => 'required|date',
            'categoria'   => 'required|string',
            'cep'         => 'required|string|max:9',
            'logradouro'  => 'required|string',
            'bairro'      => 'required|string',
            'cidade'      => 'required|string',
            'estado'      => 'required|string|max:2',
            'descricao'   => 'nullable|string',
            'foto'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'manual_pdf'  => 'nullable|mimes:pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Cria o array de dados removendo os campos de arquivo para tratar manualmente
        $dados = $request->except(['foto', 'manual_pdf']);
        $dados['user_id'] = $request->user()->id;
        $dados['local'] = $request->logradouro . ', ' . $request->cidade;

        // Lógica de Upload da Foto
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('eventos/fotos', 'public');
            $dados['foto'] = $path;
        }

        // Lógica de Upload do PDF
        if ($request->hasFile('manual_pdf')) {
            $path = $request->file('manual_pdf')->store('eventos/manuais', 'public');
            $dados['manual_pdf'] = $path;
        }

        $evento = Evento::create($dados);

        return response()->json([
            'success' => true,
            'data' => $evento,
            'message' => 'Evento criado com sucesso!'
        ], 201);
    }

    /**
     * Atualiza um evento e gerencia os arquivos antigos
     */
    public function update(Request $request, $id)
    {
        $evento = Evento::find($id);

        if (!$evento || $evento->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Acesso negado'], 403);
        }

        
        $dados = $request->except(['foto', 'manual_pdf']);

        if ($request->has('logradouro') || $request->has('cidade')) {
            $rua = $request->logradouro ?? $evento->logradouro;
            $cid = $request->cidade ?? $evento->cidade;
            $dados['local'] = $rua . ', ' . $cid;
        }

        if ($request->hasFile('foto')) {
            if ($evento->foto) { Storage::disk('public')->delete($evento->foto); }
            $dados['foto'] = $request->file('foto')->store('eventos/fotos', 'public');
        }

        if ($request->hasFile('manual_pdf')) {
            if ($evento->manual_pdf) { Storage::disk('public')->delete($evento->manual_pdf); }
            $dados['manual_pdf'] = $request->file('manual_pdf')->store('eventos/manuais', 'public');
        }

        $evento->update($dados);

        return response()->json([
            'success' => true,
            'data' => $evento,
            'message' => 'Evento atualizado!'
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $evento = Evento::find($id);
        if (!$evento || $evento->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Não autorizado'], 403);
        }

        if ($evento->foto) { Storage::disk('public')->delete($evento->foto); }
        if ($evento->manual_pdf) { Storage::disk('public')->delete($evento->manual_pdf); }

        $evento->delete();
        return response()->json(['success' => true, 'message' => 'Evento e arquivos excluídos!'], 200);
    }
}