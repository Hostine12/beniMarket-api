Route::get('/test', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API Railway fonctionne'
    ]);
});