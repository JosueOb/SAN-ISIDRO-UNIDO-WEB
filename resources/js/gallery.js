// CommonJS
const Swal = require('sweetalert2')

$(document).ready(function(){
    console.log('Formulario listo');
    let images = [];
    let renderImages = [];

    const previewImages = images =>{
        let imageItem = '';
        let counter = 0;
        // let numberOfImages = 5;

        // if(images.length <= numberOfImages){
            images.forEach(function(image, indice){
                imageItem += `
                <div class="gallery-item item-1">
                    <div class="image-cancel" data-no="${indice}"><i class="fas fa-trash-alt"></i></div>
                    <img src=${image} alt='image_${indice}'>
                </div>
                `;
                counter++;
            });
            document.getElementById('gallery').innerHTML = imageItem;
        // }else{
        //     Swal.fire({
        //         type: 'error',
        //         title: 'Fuera del límite',
        //         text: 'Recuerda que solo puedes seleccionar hasta 5 imágenes',
        //         // footer: '<a href>Why do I have this issue?</a>'
        //     })
        // }
        
        var message = images.length > 0 ? 'Imágenes seleccionadas: '+ counter : 'Seleccione alguna imagen';
        $('#images').siblings('.custom-file-label').addClass('selected').html(message);
    }

    //Al seleccionar el input file
    $('#images').on('change', function(event){
        $('#images').removeClass('is-invalid');
        //Se obtiene las imagenes del input
        var files = event.target.files;
        let numberOfImages = 5;
        let size = 1048576;//equivale a 1MB
        
        //se verifica que se haya seleccionado alguna imágen
        if(files){
            //se recorre cada archivo para verificar que sea una imágen
            [].forEach.call(files, function(file, index){
                if(images.length < numberOfImages){
                    console.log('Seleccionó una imagen');
                    if ( /\.(jpe?g|png)$/i.test(file.name) ) {
                        //Si la imagen es menor a 1MB
                        if(file.size < size){
                            images.push(file);
                            var reader = new FileReader();
                            reader.onload = function(event){
                                renderImages.push(event.target.result);
                                previewImages(renderImages);
                            }
                            reader.readAsDataURL(files.item(index));
                        }else{
                            Swal.fire({
                                type: 'error',
                                title: 'Fuera del límite de 1MB',
                                text: 'La imagen '+ file.name+' pesa '+ (file.size/size).toFixed(2) + 'MB',
                            })
                        }
                        
                    }else{
                        console.log('Archivo no permitido');
                        $('#images').addClass('is-invalid');
                        $('#images').siblings('.invalid-feedback').html('<strong> Archivo no permitido </strong>');
                    }
                }else{
                    Swal.fire({
                        type: 'error',
                        title: 'Fuera del límite de imágenes seleccionadas',
                        text: 'Recuerda que solo puedes seleccionar hasta 5 imágenes',
                    })
                }
            });
            //previewImages(renderImages);
        }
    });


    $('.gallery').on('click', '.image-cancel',function(){
        let imageIndex = $(this).data('no');
        //console.log(imageIndex);
        images.splice(imageIndex, 1);
        renderImages.splice(imageIndex,1);
        previewImages(renderImages);
    });
    //AJAX
    $('#report').on('submit', function(event){
        // Se evita el propago del submit
        event.preventDefault();
        
         //Se agrega el data del formData
        var formData = new FormData(this);
        formData.delete('images[]');

         images.forEach(function(image){
            formData.append('images[]', image);
         });

         $.ajax({
            type:'POST',
            url: '../reports/store',
            data:formData,
            cache:false,
            contentType: false,
            processData: false,
            dataType: 'JSON',
            success:function(data){

                console.log(data);

                if(data.success){
                    $('#title').removeClass('is-invalid');
                    $('#description').removeClass('is-invalid');
                    // $('#title').removeClass('is-invalid');
                    Swal.fire({
                    position: 'top-end',
                    type: 'success',
                    title: 'Informe publicado',
                    showConfirmButton: false,
                    timer: 1500,
                    allowOutsideClick: false,
                  })
                    // funciona como una redirección HTTP
                    setTimeout(function(){ 
                        window.location.replace('../profile');
                    }, 1000);
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                var errors = jqXHR.responseJSON;
                var validationErrors = errors.errors;
                
                if(validationErrors.title){
                    $('#title').addClass('is-invalid');
                    $('#title').siblings('.invalid-feedback').html('<strong>'+validationErrors['title'][0]+'</strong>');
                }else{
                    $('#title').removeClass('is-invalid');
                }
                if(validationErrors.description){
                    $('#description').addClass('is-invalid');
                    $('#description').siblings('.invalid-feedback').html('<strong>'+validationErrors['description'][0]+'</strong>');
                }else{
                    $('#description').removeClass('is-invalid');
                }

                console.log(errors.errors);
            }
        });
    });
});