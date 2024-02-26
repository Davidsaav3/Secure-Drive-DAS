import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { catchError } from 'rxjs/operators';
import { throwError } from 'rxjs';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../auth.service'; 
import { DownloaderService } from '../downloader.service';
import { DownloaderSharedService } from '../downloaderShared.service';
import { UploadService } from '../upload.service';
import { FilesService } from '../files.service';
import { MyfilesService } from '../myshare.service';
import { OtherfilesService } from '../othershare.service';
import { DomSanitizer, SafeUrl } from '@angular/platform-browser';
import { DownService } from '../down.service';
import { Router } from '@angular/router';
import { Pipe, PipeTransform } from '@angular/core';

@Pipe({ name: 'replace' })
export class ReplacePipe implements PipeTransform {
  transform(value: string, find: string, replacement: string): string {
    return value.replace(new RegExp(find, 'g'), replacement);
  }
}

@Component({
  selector: 'app-perfil',
  templateUrl: './perfil.component.html',
  styleUrls: ['./../app.component.css']
})

export class PerfilComponent implements OnInit {

  constructor(private downloaderShared: DownloaderSharedService, private downService: DownService, private sanitizer: DomSanitizer, private filesService: FilesService,private uploadService: UploadService, private downloaderService: DownloaderService, private authService:AuthService, private formBuilder: FormBuilder, private http: HttpClient, private myfilesService: MyfilesService, private otherfilesService: OtherfilesService, private router: Router) {
  }
 
  files: any[] = [];
  selectedFile: File | null = null;
  fileName: string = '';
  email: any;
  pag= 0;
  username= localStorage.getItem('username');
  id= 'p2.txt';
  archivos: any[] = [];
  archivos2: any[] = [];
  fileList: any;
  options: string[] = [];

  solicitudes: string[] = ["David","Luis","Adam"];
  profile= true;
  follow= true;
  numfollow= 0;
  numfollowers= 0;
  numimages= 0;
  profileimage= "https://via.placeholder.com/150";
  megusta= 0;

  Okshare = false;
  Notshare = false;
  Okdelete = false;
  UserNotshare = false;
  Nodelete = false;
  Noupload = false;
  Okdeleteall = false;
  NoNoupload = false;
  Okupload = false;
  mostrarDiv: boolean = false;
  parametroDeUrl: any;


  toggleDiv() {
    this.mostrarDiv = !this.mostrarDiv;
  }

  imageName = 'tu-imagen.jpg'; // Reemplaza con el nombre de tu imagen
  imageUrl1: string[] = [];

  folderPath = '/imagenes'; // Reemplaza con la ruta de tus imágenes
  images: any[] = [];
  shareForm: FormGroup = this.formBuilder.group({
    username: ['', [Validators.required]],
  });

  ngOnInit(): void { // INICILIZACIÓN
    //this.parametroDeUrl = this.router.snapshot.params['nombreDelParametro'];
    if (localStorage.getItem('username')==null) {
      //this.router.navigate(['login']);
    }
    this.cargar();
    this.fileMy();
  }

  getOptions(){ // OBTIENE USUARIOS PARA COMPARTIR
    const formData = new FormData();
    formData.append('username', this.username+``);
    return this.http.post<any>('https://proteccloud.000webhostapp.com/select.php', formData)
      .subscribe(data => {
        if (data) {
          this.options = data.usernames;
        } 
        else {
          //console.error('Formato de respuesta incorrecto', data);
        }
      }, error => {
        //console.error('Error al realizar la solicitud', error);
      });  
  }

  cargar() { // CARGA IMAGENES DEL USUARIO
    const folderPath = 'storage/' + this.username; 
    this.filesService.files(folderPath).subscribe(
      (response: any) => {
        this.archivos = response.archivos;
        for (let i = 0; i < this.archivos.length; i++) {
          const archivo = this.archivos[i];
          const filePath = this.username + '/' + archivo.nombre;
          this.downService.getFileView(filePath).subscribe(
            (url: string) => {
              this.archivos[i].url= url;
            },
            (error: any) => {
              //console.error('Error al obtener la imagen:', error);
            }
          );
        }
        //console.log(this.archivos)
      },
      (error: any) => {
        //console.error('Error al obtener la lista de archivos:', error);
      }
    );
  }  

  fileMy() { // CARGA IMAGENES DEL USUARIO
    const folderPath = 'storage/' + this.username; 
    this.myfilesService.files(folderPath, this.username+'').subscribe(
      (response: any) => {
        this.archivos2 = response.archivos;
        for (let i = 0; i < this.archivos2.length; i++) {
          const archivo = this.archivos2[i];
          const filePath = this.username + '/' + archivo.nombre;
          this.downService.getFileView(filePath).subscribe(
            (url: string) => {
              this.archivos2[i].url= url;
            },
            (error: any) => {
              //console.error('Error al obtener la imagen:', error);
            }
          );
        }
      },
      (error: any) => {
        //console.error('Error al obtener la lista de archivos:', error);
      }
    );
  }  

  ///////////////////////

  down(id :string): void { // DESCARGAR IMAGENES
    this.downService.getFileView(id).subscribe(
      (url: string) => {
        return url;
        this.imageUrl1.push(url);
      },
      (error: any) => {
        //console.error('Error al obtener la imagen:', error);
      }
    );
  }

  getSafeImageUrl(image: any): SafeUrl { // PREVISUALIZACIÓN IMAGENES
    const base64Image = image.contenido;
    const imageUrl = 'data:image/' + image.tipo + ';base64,' + base64Image;
    return this.sanitizer.bypassSecurityTrustUrl(imageUrl);
  }

  nombre(event: any) { // OBTENER NOMBRE DE USUARIO ACTUAL
    let input = event.target;
    this.fileName = input.files[0].name;
    setTimeout(() => {
      this.fileName= '';
      this.cargar();
    }, 1000);
  }

  eliminar(id: string) { // ELIMINA UNA PUBLICACIÓN
    const url = `https://proteccloud.000webhostapp.com/files.php`;
    const httpOptions = {
      headers: new HttpHeaders({ 'Content-Type': 'application/json' })
    };
    const formData = new FormData();
    formData.append('file_name', this.username+'/'+id);

      const headers = new HttpHeaders();
      this.http.post('https://proteccloud.000webhostapp.com/delete.php', formData, { headers })
        .subscribe(
          (data: any) => {
            this.Okdeleteall = true;  
            setTimeout(() => {
              this.Okdeleteall =  false;
            }, 3000);
            setTimeout(() => {
              this.cargar();
            }, 500);
          },
          (error: any) => {
            //console.error('Error al subir el archivo:', error);
            this.Okdeleteall = true;  
            setTimeout(() => {
              this.Okdeleteall =  false;
            }, 3000);
            setTimeout(() => {
              this.cargar();
            }, 500);
          }
        );
  }

  descargar(id: any) { // DESCARGAR IMAGEN
    this.downloaderService.downloader(this.username+'/'+id);
  }

  descargarShared(id: any, owner: any) { // DESCARGAR COMPARTIDA
    this.downloaderShared.downloader(owner+'/'+id, owner, this.username);
  }

  compartir(id: any) { // COMPARTIR PUBLICACIÓN
    if (this.shareForm.valid) {
      console
      const url = 'https://proteccloud.000webhostapp.com/share.php';
      const body = {
        files_user: this.username, 
        share_user: this.shareForm.get('username')?.value, 
        files_name: id
      };
      const httpOptions = {
          headers: new HttpHeaders({
              'Content-Type': 'application/x-www-form-urlencoded'
          })
      };
      this.http.post(url, JSON.stringify(body), httpOptions)
      .pipe(
          catchError((error: HttpErrorResponse) => {
              this.Notshare = true;
              setTimeout(() => {
                this.Notshare = false;
              }, 3000);
              if (error.error instanceof ErrorEvent) {
                  //console.error('Error del lado del cliente:', error.error.message);
              } else {
                  //console.error(`Código de error del servidor: ${error.status}, ` + `cuerpo del error: ${error.error}`);
              }
              return throwError('Algo salió mal; inténtalo de nuevo más tarde.');
          })
      )
      .subscribe(
          (response: any) => {
              if(response.code=="Se subio dpm"){
                this.Okshare = true;
                this.fileMy();
                
                setTimeout(() => {
                  this.Okshare = false;
                }, 3000);
              }
          },
          (error: any) => {
              //console.error('Error de solicitud:', error);
          }
      );
    }
    else{
      this.shareForm.markAllAsTouched();
    }    
  }

  noShare(id: any, num: any, otro: any) { // DEJAR DE COMPARTIR
    const formData = new FormData();
    formData.append('file_name', this.username+'/'+id+'/'+num+'/'+otro);
    const headers = new HttpHeaders();
    this.http.post('https://proteccloud.000webhostapp.com/noshare.php', formData, { headers })
      .subscribe(
        (data: any) => {
          //console.log('Respuesta del servidor:', data);
        },
        (error: any) => {
          if(error.status==200){
            this.Okdelete = true;
            this.fileMy();
            setTimeout(() => {
            this.Okdelete = false;
            }, 3000);
          }
          else{
            this.Nodelete = true;
            this.fileMy();
            setTimeout(() => {
              this.Nodelete = false;
            }, 3000);
          }
        }
      );
  }
}