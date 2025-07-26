<?php
/** @var \Laravel\Lumen\Routing\Router $router */
use App\Http\Controllers\BlokController;
use App\Http\Controllers\ParkirController;
use App\Http\Controllers\ParkingController;
use App\Http\Controllers\SlotParkirController;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix'=>'api'], function() use ($router){
    // Health check endpoint
    $router->get('health', function () {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => date('c'),
            'service' => 'sparka-backend'
        ]);
    });
    
    $router->post('login', 'AuthController@login');
    $router->post('register', 'AuthController@register');

    $router->post('login-admin', 'AdminController@loginAdmin');
    $router->post('register-admin', 'AdminController@adminRegister');

    $router->post('login-eksklusif', 'AuthHardwareController@loginEksklusif');
    $router->post('register-eksklusif', 'AuthHardwareController@registerEksklusif');
    
    // Public notification endpoints (no authentication required)
    $router->get('notifications/public/parking-status', ['uses' => 'NotificationController@getPublicParkingStatus']);
    $router->get('notifications/public/slot-availability', ['uses' => 'NotificationController@getPublicSlotAvailability']);
    
    // Public streaming endpoint for AI processing (no authentication required for testing)
    $router->post('admin/streaming/trigger-ai', ['uses' => 'StreamingController@triggerAI']);

    $router->group(['middleware' => 'admin'], function () use ($router){
        $router->get('/admin/token', 'AdminController@getToken');
        $router->post('/admin/logout', 'AdminController@logoutAdmin');

        $router->get('/admin/get-log-kendaraan', ['uses' => 'LogKendaraanController@index']);
        $router->post('/admin/create-log-kendaraan', ['uses' => 'LogKendaraanController@create']);
        $router->get('/admin/log-kendaraan/{id}', ['uses' => 'LogKendaraanController@show']);
        $router->put('/admin/update-log-kendaraan/{id}', ['uses' => 'LogKendaraanController@update']);
        $router->delete('/admin/delete-log-kendaraan/{id}', ['uses' => 'LogKendaraanController@destroy']);
        // $router->post('/admin/log-kendaraan/exit-time', ['uses' => 'LogKendaraanController@exitTime']);

        $router->get('/admin/monitor-kendaraan/{id}', ['uses' => 'MonitorController@monitorKendaraanShow']);
        $router->put('/admin/monitor-kendaraan/{id}', ['uses' => 'MonitorController@monitorKendaraanUpdate']);
        $router->delete('/admin/monitor-kendaraan/{id}', ['uses' => 'MonitorController@monitorKendaraanDestroy']);
        $router->get('/admin/monitor-kendaraan', ['uses' => 'MonitorController@monitorKendaraanIndex']);
        $router->post('/admin/monitor-kendaraan', ['uses' => 'MonitorController@monitorKendaraanCreate']);

        // $router->get('/admin/capture-image/{id}', ['uses' => 'CaptureImageController@show']);
        // $router->put('/admin/capture-image/{id}', ['uses' => 'CaptureImageController@update']);
        // $router->delete('/admin/capture-image/{id}', ['uses' => 'CaptureImageController@destroy']);
        // $router->get('/admin/capture-image', ['uses' => 'CaptureImageController@index']);
        // $router->post('/admin/capture-image', ['uses' => 'CaptureImageController@store']);

        $router->get('/admin/fakultas', ['uses' => 'FakultasController@index']);
        $router->get('/admin/fakultas-with-statistics', ['uses' => 'FakultasController@indexWithStatistics']);
        $router->post('/admin/fakultas', ['uses' => 'FakultasController@create']);
        $router->get('/admin/fakultas/{id}', ['uses' => 'FakultasController@show']);
        $router->post('/admin/fakultas/{id}', ['uses' => 'FakultasController@update']);
        $router->delete('/admin/fakultas/{id}', ['uses' => 'FakultasController@destroy']);

        $router->group(['prefix' => 'admin/fakultas/{fakultasId}'], function () use ($router) {
            $router->get('/blok', ['uses' => 'BlokController@index']);
            $router->post('/blok', ['uses' => 'BlokController@create']);
            $router->get('/blok/{id}', ['uses' => 'BlokController@show']);
            $router->post('/blok/{id}', ['uses' => 'BlokController@update']);
            $router->delete('/blok/{id}', ['uses' => 'BlokController@destroy']);
        });
    


        $router->get('/admin/slot-parkir/get-total-slot-parkir', ['uses' => 'SlotParkirController@index']);
        $router->get('/admin/slot-parkir/get-idblok-slotname-status', ['uses' => 'SlotParkirController@getIdblokStatusSlotname']);
        $router->get('/admin/slot-parkir/get-slot-terisi', ['uses' => 'SlotParkirController@getSlotTerisi']);
        $router->get('/admin/slot-parkir/get-slot-selesai', ['uses' => 'SlotParkirController@getslotSelesai']);
        $router->get('/admin/slot-parkir/get-slot-dibook-dbtlkn-stm-set', ['uses' => 'SlotParkirController@getSlotsDibookingSelesaiDibatalkan']);
        $router->get('/admin/slot-parkir/fakultas/{id_fakultas}/statistics', ['uses' => 'SlotParkirController@getStatisticsByFakultas']);

        // $router->get('/admin/slot-parkir/check-and-update', ['uses' => 'SlotParkirController@getAndCheckAndUpdate']);

        $router->group(['prefix' => 'admin/slot-parkir/{id_blok}'], function () use ($router) {
            $router->get('/get-total-slot-parkirs', ['uses' => 'SlotParkirController@index2']);
            $router->get('/get-total-data-slot', ['uses' => 'SlotParkirController@getSlotInBlok']);
            $router->get('/get-slot-depan-belakang', ['uses' => 'SlotParkirController@getSlotDepanBelakang']);
            $router->post('/create', ['uses' => 'SlotParkirController@create']);
            $router->get('/get-detail-slot/{id}', ['uses' => 'SlotParkirController@show']);
            $router->put('/update-slot/{id}', ['uses' => 'SlotParkirController@update']);
            $router->delete('/delete-slot/{id}', ['uses' => 'SlotParkirController@destroy']);
        });
        
        $router->post('/admin/slot-parkir/ubah-slot-ke-terisi', ['uses' => 'SlotParkirController@ubahSlotnameKeTerisi']);
        $router->post('/admin/slot-parkir/ubah-slot-ke-kosong', ['uses' => 'SlotParkirController@ubahSlotnameKeKosong']);
        $router->post('/admin/slot-parkir/slot-selesai/{id}', ['uses' => 'SlotParkirController@slotSelesai']);
        
        $router->get('/admin/parkir', ['uses' => 'ParkirController@index']);
        // $router->get('/admin/parkir/check-expired-bookings', ['uses' => 'ParkirController@checkAndUpdateExpiredBookings']);
        // $router->post('/admin/parkir', ['uses' => 'ParkirController@create']);
        $router->get('/admin/parkir/{id}', ['uses' => 'ParkirController@show']);
        $router->get('/admin/parkir-khusus/{id}', ['uses' => 'ParkirController@showKhusus']);
        $router->put('/admin/parkir/{id}', ['uses' => 'ParkirController@update']);
        $router->delete('/admin/parkir/{id}', ['uses' => 'ParkirController@destroy']);
        $router->post('/admin/parkir/booking-slot', ['uses' => 'ParkirController@bookingSlot']);
        $router->post('/admin/parkir/booking-slot-khusus', ['uses' => 'ParkirController@bookingSlotKhusus']);
        $router->post('/admin/parkir/batal-booking-slot/{id}', ['uses' => 'ParkirController@batalBookingSlotByParkirId']);
        $router->post('/admin/parkir/batal-booking-slot-khusus/{id}', ['uses' => 'ParkirController@batalBookingSlotKhusus']);
        $router->post('/admin/parkir/ubah-slot-ke-kosong', ['uses' => 'ParkirController@ubahSlotKeKosong']);
        $router->post('/admin/parkir/ubah-slot-ke-terisi/{id}', ['uses' => 'ParkirController@ubahSlotKeTerisi']);
        $router->post('/admin/parkir/slot-terisi/{id}', ['uses' => 'ParkirController@isiSlot']);

        $router->get('/admin/reserve', ['uses' =>'ReserveController@index']);
        $router->get('/admin/reserve/{id}', ['uses' => 'ReserveController@show']);
        $router->get('/admin/reserve/download-struk-reservasi/{id}', ['uses' => 'ReserveController@downloadStrukReservasi']);
        $router->put('/admin/reserve/{id}', ['uses' => 'ReserveController@update']);
        $router->post('/admin/reserve', ['uses' => 'ReserveController@create']);
        $router->delete('/admin/reserve/{id}', ['uses' => 'ReserveController@destroy']);

        $router->get('/admin/get-user', ['uses' => 'UserController@index']);
        // $router->post('/admin/user-create', ['uses' => 'UserController@create']);
        $router->post('/admin/user-register', ['uses' => 'UserController@register']);
        $router->post('/admin/user-login', ['uses' => 'UserController@login']);
        $router->get('/admin/get-user/{id}', ['uses' => 'UserController@show']);
        $router->put('/admin/user/{id}', ['uses' => 'UserController@update']);
        $router->delete('/admin/user/{id}', ['uses' => 'UserController@destroy']);

        $router->group(['prefix' => 'admin/blok/{fakultasId}/{blokId}'], function () use ($router) {
            $router->get('/get-cctv-data', ['uses' => 'CctvDataController@index']);
            $router->post('/create-cctv-data', ['uses' => 'CctvDataController@create']);
            $router->get('/get-detail-cctv-data/{id}', ['uses' => 'CctvDataController@show']);
            $router->put('/update-cctv-data/{id}', ['uses' => 'CctvDataController@update']);
            $router->delete('/delete-cctv-data/{id}', ['uses' => 'CctvDataController@destroy']);
        });

        $router->group(['prefix' => 'admin/part/{id_blok}'], function () use ($router) {
            $router->get('/get-parts-data', ['uses' => 'PartController@fetchData']);
            $router->post('/create-parts-data', ['uses' => 'PartController@store']);
            $router->put('/update-parts-data', ['uses' => 'PartController@update']);
            $router->delete('/delete-parts-data', ['uses' => 'PartController@destroy']);
        });

        $router->group(['prefix' => 'admin/slot-parkir/{id_part}/{id_blok}'], function () use ($router) {
            $router->post('/create-all-slot', ['uses' => 'SlotParkirController@createForAll']);
            $router->put('/update-all-slot', ['uses' => 'SlotParkirController@updateForAll']);
            $router->get('/get-slot-on-part', ['uses' => 'SlotParkirController@getSlotOnPart']);
            $router->delete('/delete-many-slot', ['uses' => 'SlotParkirController@destroyForMany']);
        });

        $router->group(['prefix' => 'admin/gateway/{id_part}/{id_blok}'], function () use ($router) {
            $router->post('/create-all-gateway', ['uses' => 'GatewayController@createForAll']);
            $router->put('/update-all-gateway', ['uses' => 'GatewayController@updateForAll']);
            $router->get('/get-gateway-on-part', ['uses' => 'GatewayController@getGatewayOnPart']);
            $router->delete('/delete-many-gateway', ['uses' => 'GatewayController@destroyForMany']);
        });

        $router->group(['prefix' => 'admin/cctv/{id_part}/{id_blok}/{id_fakultas}'], function () use ($router) {
            $router->post('/create-all-cctv', ['uses' => 'CctvDataController@createForAll']);
            $router->put('/update-all-cctv', ['uses' => 'CctvDataController@updateForAll']);
            $router->get('/get-cctv-on-part', ['uses' => 'CctvDataController@getCctvOnPart']);
            $router->delete('/delete-many-cctv', ['uses' => 'CctvDataController@destroyForMany']);
        });

        $router->group(['prefix' => 'manage-accessibility/'], function () use ($router) {
            $router->post('/create-operator', ['uses' => 'OperatorController@store']);
            $router->put('/update-operator/{id}', ['uses' => 'OperatorController@update']);
            $router->get('/get-operator', ['uses' => 'OperatorController@fetchData']);
            $router->get('/show-detail-operator/{id}', ['uses' => 'OperatorController@show']);
            $router->delete('/delete-operator/{id}', ['uses' => 'OperatorController@destroy']);
        });

        // Streaming routes for admin
        $router->post('/admin/streaming/start', ['uses' => 'StreamingController@startStream']);
        $router->post('/admin/streaming/stop', ['uses' => 'StreamingController@stopStream']);
        $router->get('/admin/streaming/status/{cctvId}', ['uses' => 'StreamingController@getStreamStatus']);
        $router->get('/admin/streaming/active', ['uses' => 'StreamingController@getActiveStreams']);
        $router->get('/admin/streaming/url/{cctvId}', ['uses' => 'StreamingController@getStreamingUrl']);
        
        // Notification routes for admin
        $router->get('/admin/notifications/parking-status', ['uses' => 'Admin\NotificationController@getParkingStatus']);
        $router->get('/admin/notifications/realtime-updates', ['uses' => 'Admin\NotificationController@getRealtimeUpdates']);
    });
    
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->get('/profile', 'UserController@profile'); // cek user sekarang
        $router->post('/logout', 'AuthController@logout');
        $router->post('/logout-eksklusif', 'AuthHardwareController@logoutEksklusif');

        $router->get('/fakultas', ['uses' => 'FakultasController@index']);
        $router->post('/fakultas', ['uses' => 'FakultasController@create']);
        $router->get('/fakultas/{id}', ['uses' => 'FakultasController@show']);
        $router->put('/fakultas/{id}', ['uses' => 'FakultasController@update']);
        $router->delete('/fakultas/{id}', ['uses' => 'FakultasController@destroy']);

        $router->get('/parkir', ['uses' => 'ParkirController@index']);
        $router->post('/parkir/booking-slot', ['uses' => 'ParkirController@bookingSlot']);
        $router->post('/parkir/batal-booking-slot/{id_slot}', ['uses' => 'ParkirController@batalBookingSlot']);
        $router->get('/parkir/get-data-pesanan/{id}', ['uses' => 'ParkirController@getPesananUsers']);
        $router->get('/parkir/{id}', ['uses' => 'ParkirController@show']);
        $router->put('/parkir/{id}', ['uses' => 'ParkirController@update']);
        $router->delete('/parkir/{id}', ['uses' => 'ParkirController@destroy']);
        $router->post('/parkir/slot-terisi/{id}', ['uses' => 'ParkirController@isiSlot']);

        $router->group(['prefix' => 'fakultas/{fakultasId}'], function () use ($router) {
            $router->get('/blok', ['uses' => 'BlokController@index']);
            $router->get('/blok/{id}', ['uses' => 'BlokController@show']);
            $router->post('/blok', ['uses' => 'BlokController@create']);
            $router->put('/blok/{id}', ['uses' => 'BlokController@update']);
            $router->delete('/blok/{id}', ['uses' => 'BlokController@destroy']);
        });


        // $router->get('/reserve', ['uses' =>'ReserveController@index']);
        // $router->get('/reserve/{id}', ['uses' => 'ReserveController@show']);
        // $router->get('/reserve/download-struk-reservasi/{id}', ['uses' => 'ReserveController@show']);
        // $router->put('/reserve/{id}', ['uses' => 'ReserveController@update']);
        // $router->post('/reserve', ['uses' => 'ReserveController@create']);
        // $router->delete('/reserve/{id}', ['uses' => 'ReserveController@destroy']);

        $router->group(['prefix' => 'slot-parkir/{id_blok}'], function () use ($router) {
            $router->get('/get-total-slot-parkirs', ['uses' => 'SlotParkirController@index2']);
            $router->get('/get-total-slot-dan-kosong', ['uses' => 'SlotParkirController@getSlotKosongDanTotal']);
            $router->get('/get-slot-terisi-dibook-dbtlkn-stm-set/{userId}', ['uses' => 'SlotParkirController@getSlotsDibookingTerisiSelesaiDibatalkanUser']);
            $router->get('/get-total-data-slot', ['uses' => 'SlotParkirController@getSlotInBlok']);
            $router->get('/get-slot-depan-belakang', ['uses' => 'SlotParkirController@getSlotDepanBelakang']);
            $router->post('/create', ['uses' => 'SlotParkirController@create']);
            $router->get('/get-detail-slot/{id}', ['uses' => 'SlotParkirController@show']);
            $router->put('/update-slot/{id}', ['uses' => 'SlotParkirController@update']);
            $router->delete('/delete-slot/{id}', ['uses' => 'SlotParkirController@destroy']);
        });

        // $router->get('/slot-parkir/get-all-slot-blok-1', ['uses' => 'SlotParkirController@getAllSlotsBlok1']);
        // $router->get('/slot-parkir', ['uses' => 'SlotParkirController@index']);
        // $router->post('/slot-parkir', ['uses' => 'SlotParkirController@create']);
        // $router->get('/slot-parkir/{id}', ['uses' => 'SlotParkirController@show']);
        // $router->put('/slot-parkir/{id}', ['uses' => 'SlotParkirController@update']);
        // $router->delete('/slot-parkir/{id}', ['uses' => 'SlotParkirController@destroy']);
        // $router->post('/slot-parkir/pilih/{id}', ['uses' => 'SlotParkirController@pilihSlot']);

        // $router->get('/eksklusif-token/slot-parkir', ['uses' => 'SlotParkirController@index']);
        // $router->post('/eksklusif-token/slot-parkir', ['uses' => 'SlotParkirController@create']);
        // $router->get('/eksklusif-token/slot-parkir/{id}', ['uses' => 'SlotParkirController@show']);
        // $router->put('/eksklusif-token/slot-parkir/{id}', ['uses' => 'SlotParkirController@update']);
        // $router->delete('/eksklusif-token/slot-parkir/{id}', ['uses' => 'SlotParkirController@destroy']);

        $router->get('/cctv-data', ['uses' => 'CctvDataController@index']);
        $router->post('/cctv-data', ['uses' => 'CctvDataController@create']);
        $router->get('/cctv-data/{id}', ['uses' => 'CctvDataController@show']);
        $router->put('/cctv-data/{id}', ['uses' => 'CctvDataController@update']);
        $router->delete('/cctv-data/{id}', ['uses' => 'CctvDataController@destroy']);

        $router->get('/get-data-user/{id}', ['uses' => 'UserController@show']);
        $router->post('/edit-data-user/{id}/update-image', ['uses' => 'UserController@updateImage']);
        $router->put('/edit-data-user/{id}', ['uses' => 'UserController@updateData']);
        $router->patch('/edit-data-user/{id}/update-plat-nomor', ['uses' => 'UserController@updatePlatNomor']);
    });
});
$router->group(['prefix'=>'api'], function() use ($router){
    $router->post('login-operator', 'OperatorController@loginOperator');
    $router->post('register-operator', 'OperatorController@registerOperator');

    $router->group(['middleware' => 'operator'], function () use ($router){
        $router->post('/operator/logout', 'OperatorController@logoutOperator');
        
        // Akses ke log kendaraan
        $router->get('/operator/get-log-kendaraan', ['uses' => 'LogKendaraanController@index']);
        $router->post('/operator/create-log-kendaraan', ['uses' => 'LogKendaraanController@create']);
        $router->get('/operator/log-kendaraan/{id}', ['uses' => 'LogKendaraanController@show']);
        $router->put('/operator/update-log-kendaraan/{id}', ['uses' => 'LogKendaraanController@update']);
        $router->delete('/operator/delete-log-kendaraan/{id}', ['uses' => 'LogKendaraanController@destroy']);

        // Akses ke fakultas
        $router->get('/operator/fakultas', ['uses' => 'FakultasController@index']);
        $router->get('/operator/fakultas/{id}', ['uses' => 'FakultasController@show']);

        // Akses ke parkir
        $router->get('/operator/parkir', ['uses' => 'ParkirController@index']);
        $router->get('/operator/parkir/{id}', ['uses' => 'ParkirController@show']);
        $router->post('/operator/parkir/booking-slot', ['uses' => 'ParkirController@bookingSlot']);
        $router->post('/operator/parkir/batal-booking-slot/{id}', ['uses' => 'ParkirController@batalBookingSlot']);
        $router->post('/operator/parkir/ubah-slot-ke-kosong', ['uses' => 'ParkirController@ubahSlotKeKosong']);
        $router->post('/operator/parkir/ubah-slot-ke-terisi/{id}', ['uses' => 'ParkirController@ubahSlotKeTerisi']);
        $router->post('/operator/parkir/slot-terisi/{id}', ['uses' => 'ParkirController@isiSlot']);

        // Akses ke slot parkir
        $router->get('/operator/slot-parkir/get-total-slot-parkir', ['uses' => 'SlotParkirController@index']);
        $router->get('/operator/slot-parkir/get-idblok-slotname-status', ['uses' => 'SlotParkirController@getIdblokStatusSlotname']);
        $router->get('/operator/slot-parkir/get-slot-terisi', ['uses' => 'SlotParkirController@getSlotTerisi']);
        $router->get('/operator/slot-parkir/get-slot-selesai', ['uses' => 'SlotParkirController@getslotSelesai']);
    });
});

// API untuk AI Integration Service
$router->group(['prefix'=>'api/ai'], function() use ($router){
    // Update parking status based on license plate detection
    $router->post('parking/update-status', 'ParkirController@updateParkingStatusByPlate');
    
    // Get parking statistics for monitoring
    $router->get('parking/stats', 'ParkirController@getParkingStats');
    
    // Log kendaraan endpoints for AI service
    $router->post('log-kendaraan', 'LogKendaraanController@createFromAI');
    $router->get('log-kendaraan', 'LogKendaraanController@index');
    
    // Health check endpoint for AI service
    $router->get('health', function () {
        return response()->json([
            'status' => 'healthy',
            'service' => 'SPARKA Backend API',
            'timestamp' => \Carbon\Carbon::now()->toISOString(),
            'version' => '1.0.0'
        ]);
    });
});

$router->group(['prefix'=>'api'], function() use ($router){
    // Check booking status by plate number
    $router->get('booking/check/{plate_number}', 'ParkirController@checkBookingByPlate');
});

$router->group(['prefix'=>'api/parking'], function() use ($router){
    // Update parking status
    $router->post('update', 'LogKendaraanController@createFromIntegration');
});

// Public image routes (no authentication required)
$router->group(['prefix'=>'api'], function() use ($router){
    // Image endpoints for displaying vehicle capture images
    $router->get('images/log-kendaraan/{id}', 'ImageController@showLogKendaraanImage');
    $router->get('images/log-kendaraan/{id}/base64', 'ImageController@getLogKendaraanImageBase64');
    $router->get('images/capture/{id}', 'ImageController@showCaptureImage');
});
