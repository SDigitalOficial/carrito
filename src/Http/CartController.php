<?php

namespace DigitalsiteSaaS\Carrito\Http;

use Dnetix\Redirection\PlacetoPay;
use Session;
use Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DigitalsiteSaaS\Carrito\Product;
use DigitalsiteSaaS\Carrito\Order;
use DigitalsiteSaaS\Carrito\Configuracion;
use DigitalsiteSaaS\Carrito\Pais;
use DigitalsiteSaaS\Carrito\OrderItem;
use DigitalsiteSaaS\Carrito\Municipio;
use DigitalsiteSaaS\Carrito\Category;
use DigitalsiteSaaS\Carrito\Transaccion;
use DigitalsiteSaaS\Carrito\Programacion;
use DigitalsiteSaaS\Carrito\Cupon;
use DigitalsiteSaaS\Carrito\Departamento;
use DigitalsiteSaaS\Pagina\Template;
use DigitalsiteSaaS\Pagina\Seo;
use DigitalsiteSaaS\Pagina\Whatsapp;
use DigitalsiteSaaS\Pagina\Page;
use App\User;
use DB;
use Input;
use Illuminate\Support\Facades\Auth;
use Excel;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Repositories\HostnameRepository;
use Hyn\Tenancy\Repositories\WebsiteRepository;
use Response;
use Redirect;
use GuzzleHttp;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Mail;
use App\Mail\Cotizador;
use DigitalsiteSaaS\Pagina\GrapeTemp;

class CartController extends Controller{
 

 protected $tenantName = null;


public function __construct()
{
if(!session()->has('cart')) session()->has('cart', array());
$hostname = app(\Hyn\Tenancy\Environment::class)->hostname();
        if ($hostname){
            $fqdn = $hostname->fqdn;
            $this->tenantName = explode(".", $fqdn)[0];
        }

}

public function show()
{
if(!$this->tenantName){
$departamento = Departamento::all();
$whatsapp = Whatsapp::all();
$seo = Seo::where('id','=',1)->get(); 
$plantilla = \DigitalsiteSaaS\Pagina\Template::all();
$menu = \DigitalsiteSaaS\Pagina\Page::whereNull('page_id')->orderBy('posta', 'asc')->get();
$cart = session()->get('cart');
$url = Configuracion::where('id', '=', 1)->get();
$iva = $this->iva();
$total = $this->total();
$subtotal = $this->subtotal();
$descuento = $this->descuento();
$plantillaes = Template::all();
$categoriapro = Category::all();
$meta = Page::where('slug','=','inicio')->get();
$programacion = Programacion::all();
$menufoot = Page::orderBy('posta', 'asc')->get();
}else{

$departamento = \DigitalsiteSaaS\Carrito\Tenant\Departamento::all();
$seo = \DigitalsiteSaaS\Pagina\Tenant\Seo::where('id','=',1)->get(); 
$meta = \DigitalsiteSaaS\Pagina\Tenant\Page::where('slug','=','1')->get();
$menu = \DigitalsiteSaaS\Pagina\Tenant\Page::whereNull('page_id')->orderBy('posta', 'asc')->get();
$cart = session()->get('cart');
$url = \DigitalsiteSaaS\Carrito\Tenant\Configuracion::where('id', '=', 1)->get();
$iva = $this->iva();
$total = $this->total();
$subtotal = $this->subtotal();
$descuento = $this->descuento();
$plantilla_dig = \DigitalsiteSaaS\Pagina\Tenant\Template::all();
$categoriapro = \DigitalsiteSaaS\Carrito\Tenant\Category::all();
$menufoot = \DigitalsiteSaaS\Pagina\Tenant\Page::orderBy('posta', 'asc')->get();
$whatsapp = \DigitalsiteSaaS\Pagina\Tenant\Whatsapp::where('id','=',1)->get();
}

$select = \DigitalsiteSaaS\Pagina\Tenant\Grapeselect::where('id','=', '1')->get();
 
 foreach($select as $select){
  $plantillas = GrapeTemp::where('id','=',$select->template)->get();
 }

 foreach($plantillas as $plantillastemp){
  $template = $plantillastemp->plantilla;
 }


return view('Templates.'.$template.'.compras.cart', compact('cart','plantilla_dig', 'total', 'menu', 'subtotal', 'iva', 'descuento', 'url', 'categoriapro', 'seo', 'departamento','whatsapp','menufoot','plantillas'));
}





public function add($id){

if(!$this->tenantName){  
$hola = Product::where('slug', $id)->get();
    }else{
    $hola = \DigitalsiteSaaS\Carrito\Tenant\Product::where('slug', $id)->get();
}
$product = json_decode($hola[0]);
    $cart =  session()->get('cart');
    $product->quantity = 1;
    $cart[$product->slug] = $product;
    session()->put('cart', $cart);
    return Redirect('/cart/show');
}


private function total()
{
$cart = session()->get('cart');
$total = 0;
if($cart == null){}
else{
foreach ($cart as $item) {
$total += $item->precioinivafin * $item->quantity;
}}

return $total;
}

private function subtotal()
{
$cart = session()->get('cart');
$subtotal = 0;
if($cart == null){}
else{
foreach($cart as $item){
$subtotal += $item->preciodescfin * $item->quantity;
}}

return $subtotal;
}


public function precioenvio(){
$precioenvio = 0;
return $precioenvio;
}


public function costoenvio()
{

if($_POST)
    {
    Session::put('miSesionTexto', Input::get('costoenvio'));
    }

      return Redirect('/cart/detail');
}





private function descuento()
{
$cart = session()->get('cart');
$descuento = 0;
if($cart == null){}
else{
foreach($cart as $item){
$descuento += $item->precio * $item->descuento/100;
}}

return $descuento;
}


private function iva()
{
$cart = session()->get('cart');
$iva = 0;
if($cart == null){}

else{
foreach($cart as $item){
$iva += $item->precioiva * $item->quantity;
}}
return $iva;
}



private function nombremunicipio()
{
 

$precio = DB::table('municipios')->where('id', '=', session()->get('miSesionTextouno'))->get();
$nombremunicipio = 0;
foreach($precio as $item){
$nombremunicipio = $item->municipio;
}

return $nombremunicipio;
}

private function nombremunicipioid()
{
 

$precio = DB::table('municipios')->where('id', '=', session()->get('miSesionTextouno'))->get();
$nombremunicipio = 0;
foreach($precio as $item){
$nombremunicipioid = $item->id;
}

return $nombremunicipioid;
}


private function nombredepartamentoid()
{
 

$precio = DB::table('municipios')->where('id', '=', session()->get('miSesionTextouno'))->get();
$nombredepartamentoid = 0;
foreach($precio as $item){
$nombredepartamentoid += $item->departamento_id;
}

return $nombredepartamentoid;
}


private function preciomunicipio()
{
 

$precio = DB::table('municipios')->where('id', '=', session()->get('miSesionTextouno'))->get();
$preciomunicipio = 0;
foreach($precio as $item){
$preciomunicipio += $item->p_municipio;
}

return $preciomunicipio;
}


public function update($producto, $cantidad){
if(!$this->tenantName){
$hola =  Product::where('slug', Request::segment(3))->get();
}else{
$hola = \DigitalsiteSaaS\Carrito\Tenant\Product::where('slug', Request::segment(3))->get();
}
$product = json_decode($hola[0]);
$cart = session()->get('cart');
$cart[$product->slug]->quantity = $cantidad;
session()->put('cart', $cart);
session()->get('cart');
return Redirect('/cart/show');
}






public function orderDetail(){
 
if(!$this->tenantName){
$departamento = Departamento::all();
$seo = Seo::where('id','=',1)->get();
$price = Order::max('id');
$suma = $price + 1;
$whatsapp = Whatsapp::all();
$configuracion = Configuracion::find(1);
$meta = Page::where('slug','=','1')->get();
$plantilla = \DigitalsiteSaaS\Pagina\Template::all();
foreach ($plantilla as $plantillas) {
 $templateweb = $plantillas->template;
}
$menufoot = Page::orderBy('posta', 'asc')->get();
$plantillaes = \DigitalsiteSaaS\Pagina\Template::all();
$meta = \DigitalsiteSaaS\Pagina\Tenant\Page::where('slug','=','1')->get();
$menu = \DigitalsiteSaaS\Pagina\Page::whereNull('page_id')->orderBy('posta', 'asc')->get();
$cart = session()->get('cart');
$total = $this->total();
$subtotal = $this->subtotal();
$iva = $this->iva();
$precioenvio = $this->precioenvio();
/*$datos = User::join('departamentos', 'departamentos.id', '=', 'users.ciudad')
             ->leftjoin('municipios', 'municipios.id', '=', 'users.region')
             ->where('users.id', '=' , Auth::user()->id)->get();
*/
$costoenvio = $this->costoenvio();
$preciomunicipio = $this->preciomunicipio();
$nombremunicipio = $this->nombremunicipio();
$descuento = $this->descuento();
/*$orderold  = Order::where('user_id', '=', Auth::user()->id)->get();*/
$categories = Pais::all();
/*$ordenes = Order::where('user_id', '=' ,Auth::user()->id)->where('estado', '=', 'PENDING')->get();*/
}else{
$departamento = \DigitalsiteSaaS\Carrito\Tenant\Departamento::all();
$seo = \DigitalsiteSaaS\Pagina\Tenant\Seo::where('id','=',1)->get();
$price = \DigitalsiteSaaS\Carrito\Tenant\Order::max('id');
$suma = $price + 1;
$configuracion = \DigitalsiteSaaS\Carrito\Tenant\Configuracion::where('id','=',1)->get();
$plantilla = \DigitalsiteSaaS\Pagina\Tenant\Template::all();
foreach ($plantilla as $plantillas) {
 $templateweb = $plantillas->template;
}
$plantillaes = \DigitalsiteSaaS\Pagina\Tenant\Template::all();
$menu = \DigitalsiteSaaS\Pagina\Tenant\Page::whereNull('page_id')->orderBy('posta', 'asc')->get();
$cart = session()->get('cart');
$total = $this->total();
$subtotal = $this->subtotal();
$iva = $this->iva();
$precioenvio = $this->precioenvio();
/*$datos = \DigitalsiteSaaS\Carrito\Tenant\User::join('departamentos', 'departamentos.id', '=', 'users.ciudad')
             ->leftjoin('municipios', 'municipios.id', '=', 'users.region')
             ->where('users.id', '=' , Auth::user()->id)->get();
*/
$costoenvio = $this->costoenvio();
$preciomunicipio = $this->preciomunicipio();
$nombremunicipio = $this->nombremunicipio();
$descuento = $this->descuento();

$orderold  = \DigitalsiteSaaS\Carrito\Tenant\Order::where('user_id', '=', '1')->get();
$ordenes = \DigitalsiteSaaS\Carrito\Tenant\Order::where('user_id', '=' ,'1')->where('estado', '=', 'PENDING')->get();
$categories = \DigitalsiteSaaS\Carrito\Tenant\Pais::all();
$meta = \DigitalsiteSaaS\Pagina\Tenant\Page::where('slug','=','1')->get();
$whatsapp = \DigitalsiteSaaS\Pagina\Tenant\Whatsapp::all();
$menufoot = \DigitalsiteSaaS\Pagina\Tenant\Page::orderBy('posta', 'asc')->get();
$plantilla_dig = \DigitalsiteSaaS\Pagina\Tenant\Template::all();

}

$select = \DigitalsiteSaaS\Pagina\Tenant\Grapeselect::where('id','=', '1')->get();
 
 foreach($select as $select){
  $plantillas = GrapeTemp::where('id','=',$select->template)->get();
 }

 foreach($plantillas as $plantillas){
  $template = $plantillas->plantilla;
 }

return view('Templates.'.$template.'.compras.order', compact('cart', 'total', 'subtotal', 'plantilla', 'menu','configuracion','price','suma', 'iva', 'descuento', 'costoenvio', 'categories', 'precioenvio', 'preciomunicipio', 'plantillaes', 'nombremunicipio', 'seo','departamento','meta','whatsapp','menufoot','plantillas','plantilla_dig'));

}

public function trash(){
if(!$this->tenantName){ 
$url = Configuracion::where('id', '=', 1)->get();
}else{
$url = \DigitalsiteSaaS\Carrito\Tenant\Configuracion::where('id', '=', 1)->get(); 
}
session()->forget('cart');
foreach ($url as $url) {
return Redirect($url->url);
}
}


public function delete($id){
if(!$this->tenantName){  
$hola = Product::where('slug', $id)->get();
}else{
$hola = \DigitalsiteSaaS\Carrito\Tenant\Product::where('slug', $id)->get();
}
$product = json_decode($hola[0]);
$cart = session()->get('cart');
unset($cart[$product->slug]);
session()->put('cart', $cart);
if(!$this->tenantName){ 
$url = Configuracion::where('id', '=', 1)->get();
dd($cart);
}else{
$url = \DigitalsiteSaaS\Carrito\Tenant\Configuracion::where('id', '=', 1)->get(); 
}
foreach ($url as $url) {
return Redirect($url->url);
}

}

/*
public function responsesite(Request $request){


$p_description = $request->input('p_description');
$p_extra1 = $request->input('p_extra1');
$p_cust_id_cliente = $request->input('p_cust_id_cliente');
$p_key = $request->input('p_key');
$p_id_invoice = $request->input('p_id_invoice');
$p_currency_code = $request->input('p_currency_code');
$p_amount_base = $request->input('p_amount_base');
$p_amount = $request->input('p_amount');
$p_tax = $request->input('p_tax');
$p_extra2 = $request->input('p_extra2');
$p_test_request = $request->input('p_test_request');
$p_url_response = $request->input('p_url_response');
$p_url_confirmation = $request->input('p_url_confirmation');
$p_confirm_method = $request->input('p_confirm_method');
$p_signature = $request->input('p_signature');
$p_billing_email = $request->input('p_billing_email');


return redirect()->away('https://secure.payco.co/checkout.php')->withInput(['p_amount'=>$p_amount,'p_description'=>$p_description,'p_extra1'=>$p_extra1,'p_cust_id_cliente'=>$p_cust_id_cliente,'p_key'=>$p_key,'p_id_invoice'=>$p_id_invoice,'p_currency_code'=>$p_currency_code,'p_amount_base'=>$p_amount_base,'p_amount'=>$p_amount,'p_tax'=>$p_tax,'p_extra2'=>$p_extra2,'p_test_request'=>$p_test_request,'p_url_response'=>$p_url_response,'p_url_confirmation'=>$p_url_confirmation,'p_confirm_method'=>$p_confirm_method,'p_signature'=>$p_signature,'p_billing_email'=>$p_billing_email]);
}
*/

public function responsesite(Request $request){
$client = new \GuzzleHttp\Client();
$p_description = $request->input('p_description');
$p_extra1 = $request->input('p_extra1');
$p_cust_id_cliente = $request->input('p_cust_id_cliente');
$p_key = $request->input('p_key');
$p_id_invoice = $request->input('p_id_invoice');
$p_currency_code = $request->input('p_currency_code');
$p_amount_base = $request->input('p_amount_base');
$p_amount = $request->input('p_amount');
$p_tax = $request->input('p_tax');
$p_extra2 = $request->input('p_extra2');
$p_test_request = $request->input('p_test_request');
$p_url_response = $request->input('p_url_response');
$p_url_confirmation = $request->input('p_url_confirmation');
$p_confirm_method = $request->input('p_confirm_method');
$p_signature = $request->input('p_signature');
$p_billing_email = $request->input('p_billing_email');

$requestapi = $client->post('https://secure.payco.co/checkout.php', [
                   'form_params' => [
                       'p_amount'=>$p_amount,
                       'p_description'=>$p_description,
                       'p_extra1'=>$p_extra1,
                       'p_cust_id_cliente'=>$p_cust_id_cliente,
                       'p_key'=>$p_key,
                       'p_id_invoice'=>$p_id_invoice,
                       'p_currency_code'=>$p_currency_code,
                       'p_amount_base'=>$p_amount_base,
                       'p_amount'=>$p_amount,
                       'p_tax'=>$p_tax,
                       'p_extra2'=>$p_extra2,
                       'p_test_request'=>$p_test_request,
                       'p_url_response'=>$p_url_response,
                       'p_url_confirmation'=>$p_url_confirmation,
                       'p_confirm_method'=>$p_confirm_method,
                       'p_signature'=>$p_signature,
                       'p_billing_email'=>$p_billing_email
                   ]
             ]);
}


public function datosesion(Request $request){

$cart = Session::get('cart');

dd($cart);
}

public function response() {
$request = request()->ref_payco;
$client = new Client(['http_errors' => false]);
$responsedg = $client->get('https://secure.epayco.co/validation/v1/reference/'.$request, [
'headers' => [
],
]);
$xmlsg = json_decode($responsedg->getBody()->getContents(), true);
$estado = $xmlsg['data']['x_respuesta'];
$id_factura = $xmlsg['data']['x_id_factura'];
$identificador = $xmlsg['data']['x_extra1'];
$codigo = $xmlsg['data']['x_cod_response'];
$estado = $xmlsg['data']['x_response'];
$fecha =  $xmlsg['data']['x_fecha_transaccion'];
$codigo_apr = $xmlsg['data']['x_approval_code'];
$referencia = $xmlsg['data']['x_ref_payco'];
$medio =  $xmlsg['data']['x_franchise'];
if(!$this->tenantName){
$plantilla = Template::all();
$menu = \DigitalsiteSaaS\Pagina\Page::whereNull('page_id')->orderBy('posta', 'desc')->get();
}else{
  $plantilla = \DigitalsiteSaaS\Pagina\Tenant\Template::all();
  $menu = \DigitalsiteSaaS\Pagina\Tenant\Page::whereNull('page_id')->orderBy('posta', 'desc')->get();
}

if(!$this->tenantName){
Order::where('identificador', $identificador)
->update(['codigo' => $referencia,
          'estado' => $estado,
          'fecha' =>  $fecha,
          'codigo_apr' => $codigo_apr,
          'medio' => $medio]);        
          }else{
\DigitalsiteSaaS\Carrito\Tenant\Order::where('identificador', $identificador)
->update(['codigo' => $referencia,
          'estado' => $estado,
          'fecha' =>  $fecha,
          'codigo_apr' => $codigo_apr,
          'medio' => $medio]);
          }

   session()->forget('cart');

       
          return view('Templates.tienda.carrito.respuesta', compact('estado','plantilla','menu','estado','referencia'));
}
       


public function responsess(Request $request){


$id_factura =  Request::input('x_id_factura');

$codigo =  Request::input('x_cod_response');
$estado =  Request::input('x_response');
$fecha =  Request::input('x_fecha_transaccion');
$codigo_apr =  Request::input('x_approval_code');
$medio =  Request::input('x_franchise');


Order::where('id', $id_factura)
          ->update(['codigo' => $codigo,
      'estado' => $estado,
      'fecha' =>  $fecha,
      'codigo_apr' => $codigo_apr,
      'medio' => $medio]);

     return Redirect ('/');
}

 public function mensajes(){ 



 Session::put('identificador', Input::get('identificador'));
 $fecha = date("Y-m-d h:i:s A");
 $cart = Session::get('cart');
 if(!$this->tenantName){
 $validacion = Order::where('identificador','=',session::get('identificador'))->count();
 $contenido = Cupon::where('codigo','=', session::get('codigo'))
 ->update([
 'estado' => 0,
 ]);
 }else{
 $validacion = \DigitalsiteSaaS\Carrito\Tenant\Order::where('identificador','=',session::get('identificador'))->count();
  $contenido = \DigitalsiteSaaS\Carrito\Tenant\Cupon::where('codigo','=', session::get('codigo'))
 ->update([
 'estado' => 0,
 ]);
 }

foreach($cart as $producto) {
}

if($validacion == 1) {

if(!$this->tenantName){
$contenido = Order::where('identificador',session::get('identificador'))
->update([
'descripcion' => $producto->description,
'cantidad' => $producto->quantity,
'subtotal' =>  $this->subtotal(),
'fecha' => $fecha,
'shipping' => $this->total(),
'iva_ord' => $this->iva(),
'cos_envio' => session::get('preciomunicipio'),
'codigo' => '000000',
'estado' => 'Pendiente',
'nombre' => session::get('nombres'),
'direccion' => session::get('direccion'),
'email' => session::get('email'),
'documento' => session::get('documento'),
'telefono' => session::get('telefono'),
'inmueble' => session::get('inmueble'),
'informacion' => session::get('informacion'),
'tipo' => session::get('porcentaje'),
'empresa' => $this->subtotal()*session::get('porcentaje')/100,
'identificador' => Input::get('identificador'),
'ciudad' => session::get('nombredepartamento'),
'departamento' => session::get('nombremunicipio'),
'codigo_apr' => '000000',
'medio' => 'N/A',
'preciodescuento' => $producto->preciodesc,
'user_id'  => '1'
]);
session()->forget('nombredepartamento');
session()->forget('nombremunicipio');
session()->forget('nombres');
session()->forget('documento');
session()->forget('direccion');
session()->forget('telefono');
session()->forget('email');
session()->forget('direnvio');
session()->forget('inmueble');
session()->forget('informacion');
session()->forget('identificador');
session()->forget('terminos');
session()->forget('cart');
session()->forget('codigo');
session()->forget('porcentaje');
session()->forget('message');

}else{
$contenido = \DigitalsiteSaaS\Carrito\Tenant\Order::where('identificador',session::get('identificador'))
->update([
'descripcion' => $producto->description,
'cantidad' => $producto->quantity,
'subtotal' =>  $this->subtotal(),
'fecha' => $fecha,
'shipping' => $this->total(),
'iva_ord' => $this->iva(),
'cos_envio' => session::get('preciomunicipio'),
'codigo' => '000000',
'estado' => 'Pendiente',
'nombre' => session::get('nombres'),
'direccion' => session::get('direccion'),
'email' => session::get('email'),
'documento' => session::get('documento'),
'telefono' => session::get('telefono'),
'inmueble' => session::get('inmueble'),
'informacion' => session::get('informacion'),
'empresa' => $this->subtotal()*session::get('porcentaje')/100,
'tipo' => session::get('porcentaje'),
'identificador' => Input::get('identificador'),
'ciudad' => session::get('nombredepartamento'),
'departamento' => session::get('nombremunicipio'),
'codigo_apr' => '000000',
'medio' => 'N/A',
'preciodescuento' => $producto->preciodesc,
'user_id'  => Auth::user()->id
]);
session()->forget('nombredepartamento');
session()->forget('nombremunicipio');
session()->forget('nombres');
session()->forget('documento');
session()->forget('direccion');
session()->forget('telefono');
session()->forget('email');
session()->forget('direnvio');
session()->forget('inmueble');
session()->forget('informacion');
session()->forget('identificador');
session()->forget('terminos');
session()->forget('cart');
session()->forget('codigo');
session()->forget('porcentaje');
session()->forget('message');

}
  }else{

  if(!$this->tenantName){
  $contenido = new Order;
  }else{
  $contenido = new \DigitalsiteSaaS\Carrito\Tenant\Order;
  }

  $contenido->descripcion = $producto->description;
  $contenido->cantidad = $producto->quantity;
  $contenido->subtotal = $this->subtotal();
  $contenido->fecha = $fecha;
  $contenido->shipping = $this->total();
  $contenido->iva_ord = $this->iva();
  $contenido->cos_envio = session::get('preciomunicipio');
  $contenido->codigo = '0000';
  $contenido->estado = 'Pendiente';
  $contenido->nombre = session::get('nombres');
  $contenido->direccion = session::get('direccion');
  $contenido->email = session::get('email');
  $contenido->documento = session::get('documento');
  $contenido->telefono = session::get('telefono');
  $contenido->inmueble = session::get('inmueble');
  $contenido->informacion = session::get('informacion');
  $contenido->identificador = session::get('identificador');
  $contenido->ciudad = session::get('nombredepartamento');
  $contenido->departamento = session::get('nombremunicipio');
  $contenido->tipo = session::get('porcentaje');
  $contenido->empresa = $this->subtotal()*session::get('porcentaje')/100;
  $contenido->codigo_apr = '000000';
  $contenido->medio = 'N/A';
  $contenido->preciodescuento = $producto->preciodesc;
  $contenido->user_id = '1';
  $contenido->save();
  session()->forget('nombredepartamento');
session()->forget('nombremunicipio');
session()->forget('nombres');
session()->forget('documento');
session()->forget('direccion');
session()->forget('telefono');
session()->forget('email');
session()->forget('direnvio');
session()->forget('inmueble');
session()->forget('informacion');
session()->forget('message');
session()->forget('terminos');
session()->forget('cart');
session()->forget('codigo');
session()->forget('porcentaje');





  }

 foreach($cart as $producto){
   $this->saveOrderItemepayco($producto, $contenido->id);  
  }
  

}



protected function saveOrderItemepayco($producto, $contenido_id)
{

if(!$this->tenantName){
OrderItem::create([
'price' => $producto->precio,
'quantity' => $producto->quantity,
'product_id' => $producto->id,
'order_id' => $order_id,
'user_id' => '1'
]);
}else{
\DigitalsiteSaaS\Carrito\Tenant\OrderItem::create([
'price' => $producto->precio,
'quantity' => $producto->quantity,
'product_id' => $producto->id,
'order_id' => $contenido_id,
'user_id' => '1',
'fechad' => session()->get('dia')
]);


}
session()->forget('identificador');
session()->forget('dia');

}



protected function saveOrder()
{
$cart = Session::get('cart');
$total = Request::input('x_amount_ok');
$subtotal = $this->subtotal();
$iva_ord = $this->iva();
$cos_envio = Request::input('x_extra2');
$descripcion = Request::input('x_description');
$codigo = Request::input('x_cod_response');
$estado = Request::input('x_response');
$nombre = Request::input('x_customer_name');
$fecha = Request::input('x_transaction_date');
$apellido = Request::input('x_customer_lastname');
$empresa = Request::input('x_extra2');
$direccion = Request::input('x_extra1');
$ciudad = $this->nombremunicipio();
$documento = Request::input('x_extra3');
$codigo_apr = Request::input('x_approval_code');
$medio = Request::input('x_franchise');
$descuento = $this->descuento();

foreach($cart as $producto) {
$subtotal += $producto->quantity * $producto->price;
}

   if(!$this->tenantName){
$order = Order::create([
'descripcion' => $descripcion,
'cantidad' => $producto->quantity,
'subtotal' => $subtotal,
'fecha' => $fecha,
'shipping' => $total,
'iva_ord' => $iva_ord,
'cos_envio' => $cos_envio,
'codigo' => $codigo,
'estado' => $estado,
'nombre' => $nombre,
'apellido' => $apellido,
'empresa' => $empresa,
'direccion' => $direccion,
'ciudad' => $ciudad,
'documento' => $documento,
'codigo_apr' => $codigo_apr,
'medio' => $medio,
'preciodescuento' => $descuento*$producto->quantity,
'user_id'  => '1'
]);
}else{
$order = \DigitalsiteSaaS\Carrito\Tenant\Order::create([
'descripcion' => $descripcion,
'cantidad' => $producto->quantity,
'subtotal' => $subtotal,
'fecha' => $fecha,
'shipping' => $total,
'iva_ord' => $iva_ord,
'cos_envio' => $cos_envio,
'codigo' => $codigo,
'estado' => $estado,
'nombre' => $nombre,
'apellido' => $apellido,
'empresa' => $empresa,
'direccion' => $direccion,
'ciudad' => $ciudad,
'documento' => $documento,
'codigo_apr' => $codigo_apr,
'medio' => $medio,
'preciodescuento' => $descuento*$producto->quantity,
'user_id'  => '1'
]);
}



foreach($cart as $producto){
$this->saveOrderItem($producto, $order->id);
}
}

protected function saveOrderItem($producto, $order_id)
{

if(!$this->tenantName){
OrderItem::create([
'price' => $producto->precio,
'quantity' => $producto->quantity,
'product_id' => $producto->id,
'order_id' => $order_id,
'user_id' => '1'
]);
}else{
\DigitalsiteSaaS\Carrito\Tenant\OrderItem::create([
'price' => $producto->precio,
'quantity' => $producto->quantity,
'product_id' => $producto->id,
'order_id' => $order_id,
'user_id' => '1',
'fechad' => session()->get('dia')
]);

\DigitalsiteSaaS\Carrito\Tenant\Pumadrive::create([
'ruta' => $producto->category_id,
'fecha' => session()->get('dia')
]);

$configmail = \DigitalsiteSaaS\Carrito\Tenant\Configuracion::where('id','=',1)->get();
foreach($configmail as $configmail){
    $configmails = $configmail->cot_email;
}

$userma = session()->get('cart');
    Mail::to($configmails)
    ->bcc($configmails)
    ->cc('darioma07@gmail.com')
    ->send(new Cotizador($userma));

session()->forget('identificador');
session()->forget('dia');

}

}






protected function generaplace(Request $request){
     
$amount = Input::get('p_amount');
$reference = Input::get('p_id_invoice');

if(!$this->tenantName){
$servicio = Configuracion::where('id', '=', 1)->get();
}else{
$servicio = \DigitalsiteSaaS\Carrito\Tenant\Configuracion::where('id', '=', 1)->get();
}

foreach ($servicio as $servicio){
$secretKey = $servicio->trankey;
$login = $servicio->login;
$moneda = $servicio->monedaplace;
$descriptionsite = $servicio->description;
$redirect = $servicio->redirect.'placetopay/pagowebrequest/'.$reference;
}

$seed = date('c');

if (function_exists('random_bytes')) {
    $nonce = bin2hex(random_bytes(16));
} elseif (function_exists('openssl_random_pseudo_bytes')) {
    $nonce = bin2hex(openssl_random_pseudo_bytes(16));
} else {
    $nonce = mt_rand();
}

$nonceBase64 = base64_encode($nonce);

$tranKey = base64_encode(sha1($nonce . $seed . $secretKey, true));
 
$Authentication = array(
    "login" =>  $login,
    "tranKey" =>  SHA1(date('c').$secretKey, false),
    "seed" =>  date('c')
   );

  $request = [
    'auth' => [
    'login' => $login,
    'seed' => date('c'),
    'nonce' => $nonceBase64,
    'tranKey' => $tranKey,
    ],

   'buyer' => [
       'name' =>  session::get('nombres'),
       'surname' =>  session::get('nombres'),
       'documentType' => 'CC',
       'document' =>  session::get('documento'),
       'email' =>  session::get('email'),
       'mobile' => '32085257364', // p_extra1
       'address' => [
           'city' => 'Bogotá',
           'street' =>  session::get('direccion'),
       ]
   ],
   'payment' => [
       'reference' => $reference,
       'description' => $descriptionsite,
       'amount' => [
            'currency' => $moneda,
            'total' => $amount
        ]

],
   
    'expiration' => date('c', strtotime('1 day')),
    'returnUrl' =>  $redirect,
    'ipAddress' => '127.0.0.1',
    'userAgent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',
];

//return $request;
 if(!$this->tenantName){
$redireccionplace = Configuracion::where('id', '=', 1)->get();
}else{
$redireccionplace = \DigitalsiteSaaS\Carrito\Tenant\Configuracion::where('id', '=', 1)->get();
}

foreach ($redireccionplace as $redireccionplace){
$url = $redireccionplace->url_produccion;
}


//Se inicia. el objeto CUrl
$ch = curl_init($url);

//creamos el json a partir del arreglo
$jsonDataEncoded = json_encode($request);
//Indicamos que nuestra petición sera Post
curl_setopt($ch, CURLOPT_POST, 1);

//para que la peticion no imprima el resultado como un echo comun, y podamos manipularlo
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//Adjuntamos el json a nuestra petición
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);


    //Agregar los encabezados del contenido
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'User-Agent: cUrl Testing'));

//Ejecutamos la petición
$result = curl_exec($ch);

//return redirect('https://test.placetopay.com/redirection')
  //        ->header('customvalue1', $request);
//return  $result;
$p_extra1 = Request::input('p_extra1');
$p_billing_country = Request::input('p_billing_country');
$p_billing_name = Request::input('p_billing_name');
$p_billing_lastname = Request::input('p_billing_lastname');
$p_billing_phone = Request::input('p_billing_phone');
$p_extra3 = Request::input('p_extra3');
$informacion = Request::input('informacion');
$inmueble = Request::input('inmueble');
$email = Request::input('email');
$telefono = Request::input('telefono');

$decode = json_decode($result, true);

$urlprocess = $decode['processUrl'];

$requestid = $decode['requestId'];

if(!$this->tenantName){
Transaccion::insert(
    array('direccion' => session::get('direccion'),'ciudad' => $p_billing_country,'nombre' => session::get('nombres'),'apellido' => $p_billing_lastname,'telefono' => session::get('telefono'), 'documento' => session::get('documento'),'referencia' => $reference, 'request_id' => $requestid, 'process_url' => $urlprocess, 'user_id' => Auth::user()->id));
}else{
\DigitalsiteSaaS\Carrito\Tenant\Transaccion::insert(
     array('direccion' => session::get('direccion'),'ciudad' => $p_billing_country,'nombre' => session::get('nombres'),'apellido' => $p_billing_lastname,'telefono' => session::get('telefono'), 'documento' => session::get('documento'),'referencia' => $reference, 'request_id' => $requestid, 'process_url' => $urlprocess, 'user_id' => Auth::user()->id));
}

//
$decodew = json_decode($result, true);
//return $decode;

  //              $var1 = $decode['reason'];
               
 

//echo $var1.'<br />';

if(!$this->tenantName){
$servicio = Configuracion::where('id', '=', 1)->get();
}else{
$servicio = \DigitalsiteSaaS\Carrito\Tenant\Configuracion::where('id', '=', 1)->get();
}

foreach ($servicio as $servicio){
$descriptionsite = $servicio->description;
}

$total = $this->total();
$subtotalweb = $this->subtotal();
$iva = $this->iva();
$descuento = $this->descuento();
$precioenvio = $this->precioenvio();
$costoenvio = $this->costoenvio();
$preciomunicipio = $this->preciomunicipio();
$nombremunicipio = $this->nombremunicipio();
$descuento = $this->descuento();


$cart = session()->get('cart');

foreach ($cart as $producto) {
$subtotalweb += $producto->quantity * $producto->precio;
}

$nombrealt = Input::get('nombrenue');
$apellidoalt = Input::get('apellidonue');
$direccionalt = Input::get('direccionnue');
$telefonoalt = Input::get('telefononue');
$inmueblealt = Input::get('inmueblenue');
$informacionalt = Input::get('informacionnue');
$emailalt = Input::get('emailnue');
$ciudadalt = Input::get('p_billing_country');

if(!$this->tenantName){
  $contenido = new Order;
  }else{
  $contenido = new \DigitalsiteSaaS\Carrito\Tenant\Order;
  }
  $contenido->descripcion = $producto->description;
  $contenido->cantidad = $producto->quantity;
  $contenido->subtotal = $this->subtotal();
  $contenido->fecha = $fecha;
  $contenido->shipping = $this->total();
  $contenido->iva_ord = $this->iva();
  $contenido->cos_envio = session::get('preciomunicipio');
  $contenido->codigo = '0000';
  $contenido->estado = 'Pendiente';
  $contenido->nombre = session::get('nombres');
  $contenido->direccion = session::get('direccion');
  $contenido->email = session::get('email');
  $contenido->documento = session::get('documento');
  $contenido->telefono = session::get('telefono');
  $contenido->inmueble = session::get('inmueble');
  $contenido->informacion = session::get('informacion');
  $contenido->identificador = Input::get('identificador');
  $contenido->ciudad =session::get('nombredepartamento');
  $contenido->departamento =session::get('nombremunicipio');
  $contenido->codigo_apr = '000000';
  $contenido->medio = 'N/A';
  $contenido->preciodescuento = $producto->preciodesc;
  $contenido->user_id = Auth::user()->id;
  $contenido->save();
  foreach($cart as $producto){
  $this->saveOrderItemplace($producto, $contenido->id);  
  }
  





     session()->forget('cart');
       session()->forget('miSesionTexto');
       session()->forget('miSesionTextouno');

//foreach ($decode as $value) {
  //        $website =  print_r($value['status']);

       // if($website == 'PENDIENTE')
        // return Redirect('Pendiente');
      //else
        //return Redirect('Oyta');
      // }
//



//return $urlprocess;
return redirect($urlprocess);


}





protected function ejecutaplace($id)
{


$seed = date('c');

  if (function_exists('random_bytes')) {
  $nonce = bin2hex(random_bytes(16));
} elseif (function_exists('openssl_random_pseudo_bytes')) {
  $nonce = bin2hex(openssl_random_pseudo_bytes(16));
} else {
  $nonce = mt_rand();
}

if(!$this->tenantName){
$servicio = Configuracion::where('id', '=', 1)->get();
}else{
$servicio = \DigitalsiteSaaS\Carrito\Tenant\Configuracion::where('id', '=', 1)->get();
}

foreach ($servicio as $servicio){
$login = $servicio->login;
$secretKey = $servicio->trankey;
$urlredir = $servicio->url;
}
$nonceBase64 = base64_encode($nonce);

$tranKey = base64_encode(sha1($nonce . $seed . $secretKey, true));

$request = [
  'auth' => [
  'login' => $login,
  'seed' => date('c'),
  'nonce' => $nonceBase64,
  'tranKey' => $tranKey,
  ],
];



//return $request;
if(!$this->tenantName){
$requestsd = Transaccion::where('referencia','=', $id)->get();
$redireccionplace = Configuracion::where('id', '=', 1)->get();
}else{
$requestsd = \DigitalsiteSaaS\Carrito\Tenant\Transaccion::where('referencia','=', $id)->get();
$redireccionplace = \DigitalsiteSaaS\Carrito\Tenant\Configuracion::where('id', '=', 1)->get();
}
foreach($requestsd as $requestsd){
foreach ($redireccionplace as $redireccionplace){
$url = $redireccionplace->url_produccion.$requestsd->request_id;

}
}


//Se inicia. el objeto CUrl
$ch = curl_init($url);

//creamos el json a partir del arreglo
$jsonDataEncoded = json_encode($request);


//Indicamos que nuestra petición sera Post
curl_setopt($ch, CURLOPT_POST, 1);

//para que la peticion no imprima el resultado como un echo comun, y podamos manipularlo
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//Adjuntamos el json a nuestra petición
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);


  //Agregar los encabezados del contenido
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'User-Agent: cUrl Testing'));

//Ejecutamos la petición
$result = curl_exec($ch);


$decodemen = json_decode($result, true);
$pago = $decodemen['payment'];
$requestsd = $decodemen['requestId'];
$estado = $decodemen['status']['status'];

$total = $this->total();
$subtotal = $this->subtotal();

if(!$this->tenantName){
$menu = \DigitalsiteSaaS\Pagina\Page::whereNull('page_id')->orderBy('posta', 'desc')->get();
$plantilla = \DigitalsiteSaaS\Pagina\Template::all();
$resultadowebpen =  Transaccion::join('orders', 'transaccion.request_id', '=', 'orders.codigo_apr')
             ->where('referencia', '=' , $id)->get();
 }else{
$menu = \DigitalsiteSaaS\Pagina\Tenant\Page::whereNull('page_id')->orderBy('posta', 'desc')->get();
$plantilla = \DigitalsiteSaaS\Pagina\Tenant\Template::all();
$resultadowebpen =  \DigitalsiteSaaS\Carrito\Tenant\Transaccion::join('orders', 'transaccion.request_id', '=', 'orders.codigo_apr')
             ->where('referencia', '=' , $id)->get();
 }            
if($pago == null AND $estado == 'REJECTED'){
if(!$this->tenantName){
Order::where('codigo_apr', '=', $requestsd)->delete();
}else{
\DigitalsiteSaaS\Pagina\Tenant\Order::where('codigo_apr', '=', $requestsd)->delete();
}

return redirect($urlredir);
}
elseif($pago == null AND $estado == 'PENDING'){

return view('carrito::pendiente', compact('resultadowebpen','plantilla','menu','subtotal','total'));
}


else{


//
$decodew = json_decode($result, true);
//return $decode;

  //              $var1 = $decode['reason'];
               
     

//echo $var1.'<br />';

 if(!$this->tenantName){
$servicio = Configuracion::where('id', '=', 1)->get();
}else{
$servicio = \DigitalsiteSaaS\Carrito\Tenant\Configuracion::where('id', '=', 1)->get();
}

foreach ($servicio as $servicio){
$descriptionsite = $servicio->description;
}



foreach($decodew['payment'] as $decodea){
  $internalre = $decodea['internalReference'];
  $medio = $decodea['paymentMethodName'];
  $var5 = $decodea['amount']['from']['total'];
  $autorizacion = $decodea['authorization'];
}




  $decode = json_decode($result, true);
  $requestsd = $decode['requestId'];
  $documentoid = $decode['request']['buyer']['document'];
  $nombre = $decode['request']['buyer']['name'];
  $apellido = $decode['request']['buyer']['surname'];
  $direccion = $decode['request']['buyer']['address']['street'];
  $ciudad = $decode['request']['buyer']['address']['city'];
  $tipodocumento = $decode['request']['buyer']['documentType'];
  $estado = $decode['status']['status'];
  $date = $decode['status']['date'];
  $mensajema = $decode['status']['message'];


 



  $total = $this->total();
$subtotalweb = $this->subtotal();
$iva = $this->iva();
$precioenvio = $this->precioenvio();
$costoenvio = $this->costoenvio();
$preciomunicipio = $this->preciomunicipio();
$nombremunicipio = $this->nombremunicipio();
$descuento = $this->descuento();
 



$cart = session()->get('cart');

if(!$this->tenantName){
$order = Order::where('codigo_apr','=', $requestsd)->update([
//'descripcion' => $descriptionsite,
//'cantidad' => $producto->quantity,
//'subtotal' => $subtotalweb,
'fecha' => $date,
//'shipping' => $total,
//'iva_ord' => $iva,
//'cos_envio' => $precioenvio,
'codigo' => $autorizacion,
'mensaje' => $mensajema,
'estado' => $estado,
//'nombre' => $nombre,
//'apellido' => $apellido,
//'direccion' => $direccion,
//'ciudad' => $ciudad,
//'documento' => $documentoid,
'codigo_apr' => $requestsd,
'medio' => $medio,
'tipo' => $tipodocumento,
//'user_id'  => Auth::user()->id
]);
}else{
$order = \DigitalsiteSaaS\Carrito\Tenant\Order::where('codigo_apr','=', $requestsd)->update([
//'descripcion' => $descriptionsite,
//'cantidad' => $producto->quantity,
//'subtotal' => $subtotalweb,
'fecha' => $date,
//'shipping' => $total,
//'iva_ord' => $iva,
//'cos_envio' => $precioenvio,
'codigo' => $autorizacion,
'mensaje' => $mensajema,
'estado' => $estado,
//'nombre' => $nombre,
//'apellido' => $apellido,
//'direccion' => $direccion,
//'ciudad' => $ciudad,
//'documento' => $documentoid,
'codigo_apr' => $requestsd,
'medio' => $medio,
'tipo' => $tipodocumento,
//'user_id'  => Auth::user()->id
]);
}



/*
foreach($cart as $producto){
$this->saveOrderItemplace($producto, $order->id);
}


     \Session::forget('cart');
//foreach ($decode as $value) {
  //        $website =  print_r($value['status']);

       // if($website == 'PENDIENTE')
        // return Redirect('Pendiente');
      //else
        //return Redirect('Oyta');
      // }
//

*/
$total = $this->total();
$subtotal = $this->subtotal();
 if(!$this->tenantName){
$menu = \DigitalsiteSaaS\Pagina\Page::whereNull('page_id')->orderBy('posta', 'desc')->get();
$plantilla = \DigitalsiteSaaS\Pagina\Template::all();
$resultadoweb =  Transaccion::join('orders', 'transaccion.request_id', '=', 'orders.codigo_apr')
             ->where('referencia', '=' , $id)->get();
}else{
$menu = \DigitalsiteSaaS\Pagina\Tenant\Page::whereNull('page_id')->orderBy('posta', 'desc')->get();
$plantilla = \DigitalsiteSaaS\Pagina\Tenant\Template::all();
$resultadoweb =  \DigitalsiteSaaS\Carrito\Tenant\Transaccion::join('orders', 'transaccion.request_id', '=', 'orders.codigo_apr')
             ->where('referencia', '=' , $id)->get();
}

    return view('carrito::respuesta', compact('resultadoweb','plantilla','menu','subtotal','total'));


}
}


protected function placenotificacion()
{

 $rest=json_decode(file_get_contents('php://input'), true);
   
 print_r($rest);

 $val = sha1 ($rest['requestId'] . $rest['status']['status'] . $rest['status']['date'] . 'oY692mksC16yNn6c');
 


 if ($val==$rest['signature']) {
 

  $seed = date('c');

  if (function_exists('random_bytes')) {
  $nonce = bin2hex(random_bytes(16));
  } elseif (function_exists('openssl_random_pseudo_bytes')) {
  $nonce = bin2hex(openssl_random_pseudo_bytes(16));
  } else {
  $nonce = mt_rand();
  }
$servicio = DB::table('configuracion')->where('id', '=', 1)->get();
foreach ($servicio as $servicio){
$secretKey = $servicio->login;
$secretKey = $servicio->trankey;
}


  $nonceBase64 = base64_encode($nonce);

  $tranKey = base64_encode(sha1($nonce . $seed . $servicio->trankey, true));

$request = [
  'auth' => [
  'login' => $servicio->login,
  'seed' => date('c'),
  'nonce' => $nonceBase64,
  'tranKey' => $tranKey,
  ],
];



//return $request;
$redireccionplace = DB::table('configuracion')->where('id', '=', 1)->get();
foreach ($redireccionplace as $redireccionplace){
$url = $redireccionplace->url_produccion.$rest['requestId'];
}


//Se inicia. el objeto CUrl
$ch = curl_init($url);

//creamos el json a partir del arreglo
$jsonDataEncoded = json_encode($request);


//Indicamos que nuestra petición sera Post
curl_setopt($ch, CURLOPT_POST, 1);

//para que la peticion no imprima el resultado como un echo comun, y podamos manipularlo
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//Adjuntamos el json a nuestra petición
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);


  //Agregar los encabezados del contenido
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'User-Agent: cUrl Testing'));

//Ejecutamos la petición
$result = curl_exec($ch);




//
$decodew = json_decode($result, true);
//return $decode;
print_r($decodew);
  //              $var1 = $decode['reason'];
               
     
//echo $var1.'<br />';
if($decodew['payment'] == null){
$order = Order::where('codigo_apr','=',  $decodew['requestId'])->update([

//'descripcion' => $descriptionsite,
//'cantidad' => $producto->quantity,
//'subtotal' => $subtotalweb,
//'fecha' => $date,
//'shipping' => $total,
//'iva_ord' => $iva,
//'cos_envio' => $precioenvio,
//'mensaje' => $mensaje,
//'codigo' => $autorizacion,
'estado' => 'REJECTED',
//'nombre' => $nombre,
//'apellido' => $apellido,
//'direccion' => $direccion,
//'ciudad' => $ciudad,
//'documento' => $documentoid,
//'codigo_apr' => $requestsd,
//'medio' => $medio,
//'tipo' => $tipodocumento,
//'user_id'  => Auth::user()->id
]);
}

else{

foreach($decodew['payment'] as $decodea){
  $internalre = $decodea['internalReference'];
  $medio = $decodea['paymentMethodName'];
  $autorizacion = $decodea['authorization'];
}
 



  $decode = json_decode($result, true);
  $requestsd = $decode['requestId'];
  $documentoid = $decode['request']['buyer']['document'];
  $nombre = $decode['request']['buyer']['name'];
  $apellido = $decode['request']['buyer']['surname'];
  $direccion = $decode['request']['buyer']['address']['street'];
  $ciudad = $decode['request']['buyer']['address']['city'];
  $tipodocumento = $decode['request']['buyer']['documentType'];
  $estado = $decode['status']['status'];
  $estado = $decode['status']['status'];
  $date = $decode['status']['date'];
  $mensaje = $decode['status']['message'];
 






$order = Order::where('codigo_apr','=',  $requestsd)->update([

//'descripcion' => $descriptionsite,
//'cantidad' => $producto->quantity,
//'subtotal' => $subtotalweb,
'fecha' => $date,
//'shipping' => $total,
//'iva_ord' => $iva,
//'cos_envio' => $precioenvio,
'mensaje' => $mensaje,
'codigo' => $autorizacion,
'estado' => $estado,
'nombre' => $nombre,
'apellido' => $apellido,
'direccion' => $direccion,
//'ciudad' => $ciudad,
'documento' => $documentoid,
'codigo_apr' => $requestsd,
'medio' => $medio,
'tipo' => $tipodocumento,
//'user_id'  => Auth::user()->id
]);

}


 
   
   }else{
   
    echo '<br>';
    echo 'Generado: '. $val;
    echo '<br>';
    echo 'muestra: feb3e7cc76939c346f9640573a208662f30704ab';
    echo '<br>';
    echo 'recibido: ' . $rest['signature'];
   }


}







protected function saveOrderItemplace($producto, $order_id){
   
       if(!$this->tenantName){
OrderItem::create([
'price' => $producto->precio,
'quantity' => $producto->quantity,
'product_id' => $producto->id,
'order_id' => $order_id,
'user_id' => Auth::user()->id
]);
   }else{
   \DigitalsiteSaaS\Carrito\Tenant\OrderItem::create([
   
'price' => $producto->precio,
'quantity' => $producto->quantity,
'product_id' => $producto->id,
'order_id' => $order_id,
'user_id' => Auth::user()->id
]);
   }

}




public function confirmacion(Request $request) {
$referencia = Request::input('x_ref_payco');
$respuesta = Request::input('x_respuesta');

{
 if(!$this->tenantName){
 Order::where('codigo', $referencia)
 ->update(['estado' => $respuesta]);        
          }else{
\DigitalsiteSaaS\Carrito\Tenant\Order::where('codigo', $referencia)
->update(['estado' => $respuesta]);
          }
}
}



     public function actionIndexweb()
    {
        if($_POST)
        {
            Session::put('subcategoria', Input::get('subcategoria'));
            Session::put('clientes', Input::get('clientes'));
            Session::put('autor', Input::get('autor'));
            Session::put('parametro', Input::get('parametro'));
            Session::put('area', Input::get('area'));
             return Redirect('/carrito');
        }
       
     
           
       
    }


       public function limpieza()
    {
       session()->forget('miSesionTexto');
       session()->forget('miSesionTextouno');


       return Redirect('/cart/detail');
    }


        public function limpiezaweb()
    {
       session()->forget('subcategoria');
       session()->forget('clientes');
       session()->forget('autor');
       session()->forget('parametro');
       session()->forget('area');

       return Redirect('/carrito');
    }


      public function registrar(){
      if(!$this->tenantName){
      $seo = Seo::where('id','=',1)->get();
      $meta = Page::where('slug','=','1')->get();
      $whatsapp = Whatsapp::all();
      $plantilla = \DigitalsiteSaaS\Pagina\Template::all();
      $plantillaes = \DigitalsiteSaaS\Pagina\Template::all();
    $terminos = \DigitalsiteSaaS\Pagina\Template::all();
$cart = session()->get('cart');
$total = $this->total();
$subtotal = $this->subtotal();
$menu = \DigitalsiteSaaS\Pagina\Page::whereNull('page_id')->orderBy('posta', 'desc')->get();
$categories = Pais::all();
$ciudades = Departamento::all();
$colors = DB::table('colors')->get();
}else{
  $seo = \DigitalsiteSaaS\Pagina\Tenant\Seo::where('id','=',1)->get();
$plantilla = \DigitalsiteSaaS\Pagina\Tenant\Template::all();
    $plantillaes = \DigitalsiteSaaS\Pagina\Tenant\Template::all();
    $terminos = \DigitalsiteSaaS\Pagina\Tenant\Template::all();
$cart = session()->get('cart');
$total = $this->total();
$subtotal = $this->subtotal();
$menu = \DigitalsiteSaaS\Pagina\Tenant\Page::whereNull('page_id')->orderBy('posta', 'desc')->get();
$ciudades = \DigitalsiteSaaS\Carrito\Tenant\Departamento::all();
$categories = \DigitalsiteSaaS\Carrito\Tenant\Pais::all();
$colors = DB::table('colors')->get();
$meta = \DigitalsiteSaaS\Pagina\Tenant\Page::where('slug','=','1')->get();
$whatsapp = \DigitalsiteSaaS\Pagina\Tenant\Whatsapp::all();
foreach ($plantilla as $plantillas) {
 $templateweb = $plantillas->template;
}
}
return view('Templates.'.$templateweb.'.carrito.registrar')->with('plantilla', $plantilla)->with('plantillaes', $plantillaes)->with('menu', $menu)->with('cart', $cart)->with('total', $total)->with('subtotal', $subtotal)->with('categories', $categories)->with('terminos', $terminos)->with('colors', $colors)->with('seo', $seo)->with('ciudades', $ciudades)->with('meta', $meta)->with('whatsapp', $whatsapp);

   

    }
//'user_id'  => Auth::user()->id

        public function detalleuser(){
        if(!$this->tenantName){
       $plantilla = \DigitalsiteSaaS\Pagina\Template::all();
       $terminos = \DigitalsiteSaaS\Pagina\Template::all();
  $cart = session()->get('cart');
  $total = $this->total();
  $subtotal = $this->subtotal();
  $ordenes = Order::where('user_id', '=' ,Auth::user()->id )->get();
      $menu = \DigitalsiteSaaS\Pagina\Page::whereNull('page_id')->orderBy('posta', 'desc')->get();
 $categories = Pais::all();
}else{
 $plantilla = \DigitalsiteSaaS\Pagina\Tenant\Template::all();
       $terminos = \DigitalsiteSaaS\Pagina\Tenant\Template::all();
  $cart = session()->get('cart');
  $total = $this->total();
  $subtotal = $this->subtotal();
  $ordenes = \DigitalsiteSaaS\Carrito\Tenant\Order::where('user_id', '=' ,Auth::user()->id )->get();
      $menu = \DigitalsiteSaaS\Pagina\Tenant\Page::whereNull('page_id')->orderBy('posta', 'desc')->get();
 $categories = \DigitalsiteSaaS\Carrito\Tenant\Pais::all();
 $meta = \DigitalsiteSaaS\Pagina\Tenant\Page::where('slug','=','1')->get();
$whatsapp = \DigitalsiteSaaS\Pagina\Tenant\Whatsapp::all();
}
 return view('carrito::detalle')->with('plantilla', $plantilla)->with('menu', $menu)->with('cart', $cart)->with('total', $total)->with('subtotal', $subtotal)->with('categories', $categories)->with('terminos', $terminos)->with('ordenes', $ordenes)->with('meta', $meta)->with('whatsapp', $whatsapp);

    }

    public function webdepartamentos()
{
$cat_id = Input::get('cat_id');
if(!$this->tenantName){
$subcategories = \DigitalsiteSaaS\Carrito\Departamento::where('pais_id', '=', $cat_id)->get();
}else{
$subcategories = \DigitalsiteSaaS\Carrito\Tenant\Departamento::where('pais_id', '=', $cat_id)->get();
}
return Response::json($subcategories);
}

public function webmunicipios()
{
$cat_id = Input::get('cat_id');
if(!$this->tenantName){
        $subcategories = Municipio::where('departamento_id', '=', $cat_id)->get();
    }else{
    $subcategories = \DigitalsiteSaaS\Carrito\Tenant\Municipio::where('departamento_id', '=', $cat_id)->get();
    }
        return Response::json($subcategories);
}

public function filtrowe()
{
$cat_id = Input::get('cat_id');
if(!$this->tenantName){
       $subcategories = Category::where('categoriapro_id', '=', $cat_id)->get();
    }else{
    $subcategories = \DigitalsiteSaaS\Carrito\Tenant\Category::where('categoriapro_id', '=', $cat_id)->get();
    }
        return Response::json($subcategories);
}





public function actionIndex()
    {
    if($_POST)
    {
    Session::put('miSesionTexto', Input::get('ciudad'));
            Session::put('miSesionTextouno', Input::get('municipio'));
    }
    if(Input::get('pais') == null){
        return view('index');
        }
    else{
    return Redirect('/cart/detail');
    }
    }



public function importExport()
{
return view('importExport');
}


public function downloadExcel($type)
{
$data = User::where('rol_id','=','2')->get()->toArray();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
return Excel::create('Listado-Usuarios', function($excel) use ($data) {
$excel->sheet('Usuario', function($sheet) use ($data)
       {
$sheet->fromArray($data);
       });
})->download($type);
}


public function importExcel()
{
if(Input::hasFile('import_file')){
$path = Input::file('import_file')->getRealPath();
$data = Excel::load($path, function($reader) {
})->get();
if(!empty($data) && $data->count()){
foreach ($data as $key => $value) {
$insert[] = [
'name' => $value->name,
'last_name' => $value->last_name,
'tipo_documento' => $value->tipo_documento,
'documento' => $value->documento,
'email' => $value->email,
'address' => $value->address,
'inmueble' => $value->inmueble,
'numero' => $value->numero,
'codigo' => $value->codigo,
'phone' => $value->phone,
'celular' => $value->celular,
'fax' => $value->fax,
'compania' => $value->compania,
'pais' => $value->pais,
'ciudad' => $value->ciudad,
'region' => $value->region,
'rol_id' => $value->rol_id,
'password' => $value->password,
'remember_token' => $value->remember_token

];
}
if(!empty($insert)){
DB::table('users')->insert($insert);
return Redirect('gestion/carrito')->with('status', 'ok_create');
}
}
}
return back();
}




public function importExportmun()
{
return view('importExport');
}


public function datosusuario(){
 if($_POST){
  Session::put('nombres', Input::get('nombres'));
  Session::put('documento', Input::get('documento'));
  Session::put('direccion', Input::get('direccion'));
  Session::put('telefono', Input::get('telefono'));
  Session::put('email', Input::get('email'));
  Session::put('direnvio', Input::get('direnvio'));
  Session::put('inmueble', Input::get('inmueble'));
  Session::put('informacion', Input::get('informacion'));
  Session::put('identificador', Input::get('identificador'));
  }   
  return Redirect('/cart/detail');
  }

public function downloadExcelmun($type)
{
$data = Municipio::get()->toArray();
return Excel::create('Listado-Municipios', function($excel) use ($data) {
$excel->sheet('Municipios', function($sheet) use ($data)
       {
$sheet->fromArray($data);
       });
})->download($type);
}


public function importExcelmun()
{
if(Input::hasFile('import_file')){
$path = Input::file('import_file')->getRealPath();
$data = Excel::load($path, function($reader) {
})->get();
if(!empty($data) && $data->count()){
foreach ($data as $key => $value) {
$insert[] = [
'municipio' => $value->municipio,
'estado' => $value->estado,
'departamento_id' => $value->departamento_id,
'p_municipio' => $value->p_municipio
];
}
if(!empty($insert)){
DB::table('municipios')->insert($insert);
return Redirect('gestion/carrito/envio')->with('status', 'ok_create');
}
}
}
return back();
}



public function importExportpro()
{
return view('importExport');
}


public function downloadExcelpro($type)
{
$data = Product::get()->toArray();
return Excel::create('Listado-Productos', function($excel) use ($data) {
$excel->sheet('Products', function($sheet) use ($data)
       {
$sheet->fromArray($data);
       });
})->download($type);
}


public function importExcelpro()
{
if(Input::hasFile('import_file')){
$path = Input::file('import_file')->getRealPath();
$data = Excel::load($path, function($reader) {
})->get();
if(!empty($data) && $data->count()){
foreach ($data as $key => $value) {
$insert[] = [
'municipio' => $value->municipio,
'estado' => $value->estado,
'departamento_id' => $value->departamento_id,
'p_municipio' => $value->p_municipio
];
}
if(!empty($insert)){
DB::table('products')->insert($insert);
return Redirect('gestion/carrito/categoria')->with('status', 'ok_create');
}
}
}
return back();
}




}