import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { catchError } from 'rxjs/operators';
import { throwError } from 'rxjs';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../auth.service'; 
import { DownloaderService } from '../downloader.service';
import { UploadService } from '../upload.service';
import { FilesService } from '../files.service';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./../app.component.css']
})

export class HomeComponent  implements OnInit {

  constructor(private filesService: FilesService,private uploadService: UploadService, private downloaderService: DownloaderService, private authService:AuthService, private formBuilder: FormBuilder, private router: Router, private http: HttpClient) { }
 
  files: any[] = [];
  selectedFile: File | null = null;
  fileName: string = '';
  email: any;
  pag= 0;
  username= localStorage.getItem('username');
  id= 'p2.txt';
  archivos: any[] = [];
  fileList: any;

  shareForm: FormGroup = this.formBuilder.group({
    username: ['', [Validators.required]],
  });

  archivos2 = {
    archivo_mio: [
      {
        id: 0,
        nombre: 'p2.txt',
        tamano: '100',
        tipo: 'JPG',
        user: 'Davidsaav',
      },
      {
        id: 1,
        nombre: 'Archivo 2',
        tamano: '150',
        tipo: 'MP4',
        user: 'Davidsaav',
      },
      {
        id: 2,
        nombre: 'Archivo 3',
        tamano: '120',
        tipo: 'MP3',
        user: 'Davidsaav',
      }
    ],
    archivo_pormi: [
      {
        id: 0,
        nombre: 'Archivo 1',
        tamano: '100',
        tipo: 'JPG',
        user: 'Davidsaav',
      },
      {
        id: 1,
        nombre: 'Archivo 2',
        tamano: '150',
        tipo: 'MP4',
        user: 'Davidsaav',
      }
    ],
    archivo_conmigo: [
      {
        id: 0,
        nombre: 'Archivo 1',
        tamano: '100',
        tipo: 'JPG',
        user: 'Davidsaav',
      }
    ],
  };

  ngOnInit(): void {
    this.cargar();
  }

  cargar() {
    const folderPath = 'storage/'+this.username; 
    this.filesService.files(folderPath).subscribe(
      (response: any) => {
        this.archivos = response.archivos;
        console.log(this.archivos)
      },
      (error: any) => {
        console.error('Error al obtener la lista de archivos:', error);
      }
    );
  }

  nombre(event: any) { // FILE
    let input = event.target;
    this.fileName = input.files[0].name;
    this.upload(event)
    setTimeout(() => {
      this.fileName= '';
      this.cargar();
    }, 1000);
  }
  
  logout(): void { // CERRRA SESIÓN
    this.authService.setAuthenticated(false);
    localStorage.removeItem('username');
    localStorage.removeItem('auth');
  }

  upload(event: any) {
    const file = event.target.files && event.target.files[0];
    this.uploadService.upload(file, this.username).subscribe(
      response => {
        console.log(response.files)
        if (response.files) {
          console.log(response)
          this.fileList = response.files;
        } else {
          console.error('Error al obtener archivos:', response);
        }
      },
      error => {
        console.error('Error al hacer la solicitud:', error);
      }
    );
  }

  eliminar(id: string) { /////////////// ELIMIANR ARCHIVO ///////////////
    console.log(id)
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
            console.log('Respuesta del servidor:', data);
            setTimeout(() => {
              this.cargar();
            }, 500);
          },
          (error: any) => {
            console.error('Error al subir el archivo:', error);

          }
        );
  }

  descargar(id: any) { /////////////// DESCARGAR ///////////////
    console.log(id)
    this.downloaderService.downloader(this.username+'/'+id);
    
  }

  compartir(id: any) { /////////////// COMPARTIR ///////////////
    if (this.shareForm.valid) {
      console
      const url = 'https://proteccloud.000webhostapp.com/share.php';
      const body = {
        username: this.username, 
        username_share: this.shareForm.get('username')?.value, 
        id_doc: id
      };
      console.log(body)
      const httpOptions = {
          headers: new HttpHeaders({
              'Content-Type': 'application/x-www-form-urlencoded'
          })
      };
      console.log(body)
      this.http.post(url, JSON.stringify(body), httpOptions)
      .pipe(
          catchError((error: HttpErrorResponse) => {
              if (error.error instanceof ErrorEvent) {
                  console.error('Error del lado del cliente:', error.error.message);
              } else {
                  console.error(
                      `Código de error del servidor: ${error.status}, ` +
                      `cuerpo del error: ${error.error}`);
              }
              return throwError('Algo salió mal; inténtalo de nuevo más tarde.');
          })
      )
      .subscribe(
          (response: any) => {
              console.log('Respuesta:', response);
              if(response.code==100){
                this.cargar();
              }
          },
          (error: any) => {
              console.error('Error de solicitud:', error);
          }
      );
    }
    else{
      this.shareForm.markAllAsTouched();
    }    
  }

  noCompartir(id: any) { //////////////// DEJAR DE COMPARTIR ///////////////
    console.log(id)
    const url = `https://proteccloud.000webhostapp.com/share.php/${id}`;
    const httpOptions = {
      headers: new HttpHeaders({ 'Content-Type': 'application/json' })
    };
    this.http.delete(url, httpOptions).subscribe(          
      (response: any) => {
        console.log('Respuesta:', response);
        if(response.code==100){
          this.cargar();
        }
    },
    (error: any) => {
        console.error('Error de solicitud:', error);
    });
  }
}