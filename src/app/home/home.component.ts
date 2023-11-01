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
  username= localStorage.getItem('username');
  id= 0;
  
  shareForm: FormGroup = this.formBuilder.group({
    username: ['', [Validators.required]],
  });

  archivos = {
    archivo_mio: [
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

  cargar(){ /////////////// INIT ///////////////
    const url = 'https://proteccloud.000webhostapp.com/files.php/'+this.username;
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

  nombre(event: any) { // FILE
    let input = event.target;
    this.fileName = input.files[0].name;
    setTimeout(() => {
      this.fileName= '';
    }, 2000);
  }
  
  logout(): void {
    this.authService.setAuthenticated(false);
    localStorage.removeItem('username');
    localStorage.removeItem('auth');
  }

  upload(): void { /////////////// SUBIR ARCHIVO ///////////////
    if (this.selectedFile) {
      const uploadData = new FormData();
      uploadData.append('file', this.selectedFile, this.selectedFile.name);
      fetch('https://proteccloud.000webhostapp.com/files.php', {
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
        if(data.code==100){
          this.cargar();
        }
      })
      .catch(error => {
        console.error('Error:', error);
        throw error;
      });
    }
  }

  eliminar(id: number) { /////////////// ELIMIANR ARCHIVO ///////////////
    console.log(id)
    const url = `https://proteccloud.000webhostapp.com/files.php/${id}`;
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

  descargar(id: any) { /////////////// DESCARGAR ///////////////
    console.log(id)
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
