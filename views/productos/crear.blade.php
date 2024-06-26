 @extends ('adminsite.layout')
 

   @section('cabecera')
    @parent
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    @stop


  @section('ContenidoSite-01')

   <div class="content-header">
     <ul class="nav-horizontal text-center">
      <li>
      <a href="/gestion/carrito/dashboard"><i class="fa fa-keyboard-o"></i> Dashboard</a>
      </li>
      <li>
       <a href="/gestion/carrito"><i class="gi gi-parents"></i> Usuarios</a>
      </li>
      <li>
       <a href="/gestion/carrito/categorias"><i class="fa fa-th-list"></i> Categorias</a>
      </li>
      <li>
       <a href="/gestion/carrito/epayco"><i class="fa fa-pencil-square-o"></i>Ordenes</a>
      </li>
      <li>
       <a href="/gestion/carrito/autores"><i class="fa fa-child"></i>Autores</a>
      </li>
    
      <li>
       <a href="/gestion/carrito/crearconfiguracion"><i class="fa fa-clipboard"></i>Configurar</a>
      </li>
      <li>
       <a href="/gestion/carrito/terminos"><i class="fa fa-clipboard"></i>Terminos y condiciones</a>
      </li>
     </ul>
    </div>

 <div class="container">
  <?php $status=Session::get('status'); ?>
  @if($status=='ok_create')
   <div class="alert alert-success">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>Usuario registrado con éxito</strong>
   </div>
  @endif

  @if($status=='ok_delete')
   <div class="alert alert-danger">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>Usuario eliminado con éxito</strong>
   </div>
  @endif

  @if($status=='ok_update')
   <div class="alert alert-warning">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>Usuario actualizado con éxito</strong>
   </div>
  @endif

 </div>



<div class="container">
  <div class="row">
                            <div class="col-md-12">
                                <!-- Basic Form Elements Block -->
                                <div class="block">
                                    <!-- Basic Form Elements Title -->
                                    <div class="block-title">
                                        <div class="block-options pull-right">
                                            
                                        </div>
                                        <h2><strong>Crear</strong> Texto</h2>
                                    </div>
                                    <!-- END Form Elements Title -->
                                    
                                    <!-- Basic Form Elements Content -->
                                     {{ Form::open(array('method' => 'GET','class' => 'form-horizontal','id' => 'defaultForm', 'url' => array('gestion/productos/crearproducto'))) }}

                                        
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-text-input">Nombre</label>
                                            <div class="col-md-9">
                                                {{Form::text('nombre', '', array('class' => 'form-control','placeholder'=>'Ingrese Nombre','required' => 'required'))}}
                                            </div>
                                        </div>

                                         <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-text-input">Descripción</label>
                                            <div class="col-md-9">
                                                {{Form::text('descripcion', '', array('class' => 'form-control','placeholder'=>'Ingrese Descripción','required' => 'required'))}}
                                            </div>
                                        </div>

                                        

                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-text-input">Contenido</label>
                                            <div class="col-md-9">
                                                {{Form::textarea('contenido', '', array('class' => 'ckeditor','id' => 'editor','placeholder'=>'Ingrese contenido'))}}
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-text-input">Cantidad Stock</label>
                                            <div class="col-md-9">
                                                {{Form::text('stock', '0', array('class' => 'form-control','placeholder'=>'Ingrese Cantidad Stock','required' => 'required'))}}
                                            </div>
                                        </div>

                                         <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-text-input">Año publicación</label>
                                            <div class="col-md-9">
                                                {{Form::text('ano', date('Y'), array('class' => 'form-control','placeholder'=>'Ingrese año publicación','required' => 'required'))}}
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-text-input">Referencia producto</label>
                                            <div class="col-md-9">
                                                {{Form::text('referencia', '', array('class' => 'form-control','placeholder'=>'Ingrese año publicación','required' => 'required'))}}
                                            </div>
                                        </div>                                    

                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-text-input">Precio</label>
                                            <div class="col-md-9">
                                                {{Form::text('precio', '', array('class' => 'form-control','placeholder'=>'Ingrese Precio','required' => 'required'))}}
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-text-input">Porcentaje de Descuento</label>
                                            <div class="col-md-9">
                                                {{Form::text('descuento', '0', array('class' => 'form-control','placeholder'=>'Ingrese Descuento','required' => 'required'))}}
                                            </div>
                                        </div>
   
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-text-input">Iva</label>
                                            <div class="col-md-9">
                                                {{Form::text('iva', '0', array('class' => 'form-control','placeholder'=>'Ingrese Iva','required' => 'required'))}}
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-select">Visualización</label>
                                            <div class="col-md-9">
                                                {{ Form::select('posicion', [
                                                   '0' => 'Disponible para comprar',
                                                   '1' => 'No Disponible para comprar'], null, array('class' => 'form-control')) }}
                                             </div>
                                        </div>

                

                                        @foreach($categoria as $categoria)
                                        {{Form::hidden('categoriapro', $categoria->categoriapro_id, array('class' => 'form-control','placeholder'=>'Ingrese Iva'))}}
                                        @endforeach


                                       <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-text-input">Parametro</label>
                                            <div class="col-md-9">
                                                <select name="parametro" class="form-control">
                                                   <option value="" selected="selected" disabled>Seleccione parametro</option>
                                                  @foreach($parametros as $parametros)
                                                 <option value="{{$parametros->id}}">{{$parametros->parametro}}</option>
                                                  @endforeach
                                                </select>
                                            </div>
                                        </div>
                                

                                          <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-text-input">Autor</label>
                                            <div class="col-md-9">
                                                <select name="autor" class="form-control" required>
                                                   <option value="" selected="selected" disabled>Seleccione autor</option>
                                                  @foreach($autores as $autores)
                                                 <option value="{{$autores->id}}">{{$autores->nombre}}</option>
                                                  @endforeach
                                                </select>
                                            </div>
                                        </div>


                                    
                                       <div class="form-group">
                                         <label class="col-md-3 control-label" for="example-password-input">Imagen</label>
                                          <div class="col-md-9">
                                           <div class="input-group">
                                            <input type="text" id="image_label" class="form-control" name="FilePath" placeholder="Seleccionar imagen" aria-label="Image" aria-describedby="button-image" required>
                                            <span class="input-group-btn"><button class="btn btn-primary" type="button" id="button-image">Seleccionar imagen</button></span>
                                           </div>
                                          </div>
                                        </div>

                                        <div class="form-group">
                                         <label class="col-md-3 control-label" for="example-password-input">Imagen</label>
                                          <div class="col-md-9">
                                           <div class="input-group">
                                            <input type="text" id="image_labela" class="form-control" name="FilePatha" placeholder="Seleccionar imagen" aria-label="Image" aria-describedby="button-image">
                                            <span class="input-group-btn"><button class="btn btn-primary" type="button" id="button-imagea">Seleccionar imagen</button></span>
                                           </div>
                                          </div>
                                        </div>

                                        <div class="form-group">
                                         <label class="col-md-3 control-label" for="example-password-input">Imagen</label>
                                          <div class="col-md-9">
                                           <div class="input-group">
                                            <input type="text" id="image_labelb" class="form-control" name="FilePathb" placeholder="Seleccionar imagen" aria-label="Image" aria-describedby="button-image">
                                            <span class="input-group-btn"><button class="btn btn-primary" type="button" id="button-imageb">Seleccionar imagen</button></span>
                                           </div>
                                          </div>
                                        </div>

                                        <div class="form-group">
                                         <label class="col-md-3 control-label" for="example-password-input">Imagen</label>
                                          <div class="col-md-9">
                                           <div class="input-group">
                                            <input type="text" id="image_labelc" class="form-control" name="FilePathc" placeholder="Seleccionar imagen" aria-label="Image" aria-describedby="button-image">
                                            <span class="input-group-btn"><button class="btn btn-primary" type="button" id="button-imagec">Seleccionar imagen</button></span>
                                           </div>
                                          </div>
                                        </div>

                                        <div class="form-group">
                                         <label class="col-md-3 control-label" for="example-password-input">Imagen</label>
                                          <div class="col-md-9">
                                           <div class="input-group">
                                            <input type="text" id="image_labeld" class="form-control" name="FilePathd" placeholder="Seleccionar imagen" aria-label="Image" aria-describedby="button-image">
                                            <span class="input-group-btn"><button class="btn btn-primary" type="button" id="button-imaged">Seleccionar imagen</button></span>
                                           </div>
                                          </div>
                                        </div>


                                         <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-select">Visualización</label>
                                            <div class="col-md-9">
                                                {{ Form::select('nivel', [
                                                   '1' => 'Visible',
                                                   '0' => 'No Visible'], null, array('class' => 'form-control')) }}
                                             </div>
                                        </div>


                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-password-input">Responsive</label>
                                            <div class="col-md-9">
                                                 {{Form::text('responsive', 'col-xs-12 col-sm-12 col-md-12 col-lg-12', array('class' => 'form-control','placeholder'=>'Ingrese responsive'))}}
                                            </div>
                                        </div>

                                          <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-text-input">Facebook</label>
                                            <div class="col-md-9">
                                                {{Form::text('facebook', '', array('class' => 'form-control','placeholder'=>'Ingrese Facebook'))}}
                                            </div>
                                        </div>

                                         <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-text-input">Youtube</label>
                                            <div class="col-md-9">
                                                {{Form::text('youtube', '', array('class' => 'form-control','placeholder'=>'Ingrese Youtube'))}}
                                            </div>
                                        </div>

                                         <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-text-input">Pinterest</label>
                                            <div class="col-md-9">
                                                {{Form::text('pinterest', '', array('class' => 'form-control','placeholder'=>'Ingrese Pinterest'))}}
                                            </div>
                                        </div>

                                         <div class="form-group">
                                            <label class="col-md-3 control-label" for="example-text-input">Instagram</label>
                                            <div class="col-md-9">
                                                {{Form::text('instagram', '', array('class' => 'form-control','placeholder'=>'Ingrese instagram'))}}
                                            </div>
                                        </div>

                                         
                                           <input type="hidden" name="peca" value="{{Request::segment(4)}}"></input>

                                        <div class="form-group form-actions">
                                            <div class="col-md-9 col-md-offset-3">
                                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-angle-right"></i> Crear</button>
                                                <button type="reset" class="btn btn-sm btn-warning"><i class="fa fa-repeat"></i> Cancelar</button>
                                            </div>
                                        </div>
                                    {{ Form::close() }}
                                
                                </div>
                                <!-- END Basic Form Elements Block -->
                            </div>
                          </div>
                          
</div>



<script src="/validaciones/carrito/crear-producto.js" type="text/javascript"></script>


<script>
    document.addEventListener("DOMContentLoaded", function() {

    document.getElementById('button-image').addEventListener('click', (event) => {
      event.preventDefault();

      inputId = 'image_label';

      window.open('/file-manager/fm-button', 'fm', 'width=1400,height=800');
    });

    document.getElementById('button-imagea').addEventListener('click', (event) => {
      event.preventDefault();

      inputId = 'image_labela';

      window.open('/file-manager/fm-button', 'fm', 'width=1400,height=800');
    });

    // second button
    document.getElementById('button-imageb').addEventListener('click', (event) => {
      event.preventDefault();

      inputId = 'image_labelb';

      window.open('/file-manager/fm-button', 'fm', 'width=1400,height=800');
    });

    document.getElementById('button-imagec').addEventListener('click', (event) => {
      event.preventDefault();

      inputId = 'image_labelc';

      window.open('/file-manager/fm-button', 'fm', 'width=1400,height=800');
    });

    document.getElementById('button-imaged').addEventListener('click', (event) => {
      event.preventDefault();

      inputId = 'image_labeld';

      window.open('/file-manager/fm-button', 'fm', 'width=1400,height=800');
    });

  });

  // input
  let inputId = '';

  // set file link
  function fmSetLink($url) {
    document.getElementById(inputId).value = $url;
  }
</script>


<script src="https://cdn.ckeditor.com/4.11.2/full/ckeditor.js"></script>

<script>
  CKEDITOR.replace( 'editor', {filebrowserImageBrowseUrl: '/file-manager/ckeditor'});
</script>

  @stop