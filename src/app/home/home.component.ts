import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { catchError } from 'rxjs/operators';
import { throwError } from 'rxjs';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../auth.service'; 

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./../app.component.css']
})

export class HomeComponent  implements OnInit {

  constructor(private authService:AuthService, private formBuilder: FormBuilder, private router: Router, private http: HttpClient) { }
  
  files: any[] = [];
  selectedFile: File | null = null;
  fileName: string = '';
  email: any;
  pag= 0;
  username= 'davidsaav3';

  shareForm: FormGroup = this.formBuilder.group({
    email: ['', [Validators.required, Validators.email]],
  });

  imageList: any[] = [
    { url: 'https://via.placeholder.com/400' },
    { url: 'https://via.placeholder.com/300' },
    { url: 'https://via.placeholder.com/300' },
    { url: 'https://via.placeholder.com/300' },
    { url: 'https://via.placeholder.com/300' },
    { url: 'https://via.placeholder.com/300' },
    { url: 'https://via.placeholder.com/300' },
  ];

  ngOnInit(): void { /////////////// INIT ///////////////
    fetch('http://ejemplo.com/api/userdata') 
      .then(response => {
        if (!response.ok) {
          throw new Error('Error al obtener los datos del usuario.');
        }
        return response.json();
      })
      .then(data => {
        this.email = data.email;
        this.files = data.files;
      })
      .catch(error => {
        console.error('Error:', error);
      });
      //
      this.shareForm = this.formBuilder.group({
        email: ['', [Validators.required, Validators.email]]
      });
  }

  showFileName(event: any) { // FILE
    let input = event.target;
    this.fileName = input.files[0].name;
    setTimeout(() => {
      this.fileName= '';
    }, 2000);
  }
  
  logout(): void {
    this.authService.setAuthenticated(false);
  }

  upload(): void { /////////////// SUBIR ARCHIVO ///////////////
    if (this.selectedFile) {
      const uploadData = new FormData();
      uploadData.append('file', this.selectedFile, this.selectedFile.name);

      fetch('http://ejemplo.com/api/upload', {
        method: 'POST',
        body: uploadData
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Error al cargar archivo.');
        }
        return response.json();
      })
      .then(data => {
      })
      .catch(error => {
        console.error('Error:', error);
        throw error;
      });
    }
  }

  eliminar(id: number) { /////////////// ELIMIANR ARCHIVO ///////////////
    const url = `https://proteccloud.000webhostapp.com/files.php/${id}`;
    const httpOptions = {
      headers: new HttpHeaders({ 'Content-Type': 'application/json' })
    };
    this.http.delete(url, httpOptions).subscribe(() => {
      console.log('Archivo eliminado con éxito');
    }, error => {
      console.error('Error al eliminar el archivo', error);
    });
  }

  descargar(id: any) { /////////////// DESCARGAR ///////////////
    const url = 'https://proteccloud.000webhostapp.com/files.php/'+id;
    this.http.get(url, { responseType: 'blob' }).subscribe((response: any) => {
      const blob = new Blob([response], { type: 'application/octet-stream' });
      const urlDescarga = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = urlDescarga;
      link.download = 'descarga';
      link.click();
      window.URL.revokeObjectURL(urlDescarga);
    });
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
              }
          },
          (error: any) => {
              console.error('Error de solicitud:', error);
              // Aquí puedes realizar acciones adicionales en caso de error de solicitud
          }
      );
    }
    else{
      this.shareForm.markAllAsTouched();
    }    
  }

  noCompartir(id: any) { //////////////// DEJAR DE COMPARTIR ///////////////
    const url = `https://proteccloud.000webhostapp.com/share.php/${id}`;
    const httpOptions = {
      headers: new HttpHeaders({ 'Content-Type': 'application/json' })
    };
    this.http.delete(url, httpOptions).subscribe(() => {
      console.log('Archivo descompartido con éxito');
    }, error => {
      console.error('Error al eliminar el archivo', error);
    });
  }
}
