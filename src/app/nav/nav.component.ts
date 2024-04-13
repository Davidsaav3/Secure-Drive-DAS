import { Component, OnInit } from '@angular/core';
import { AuthService } from '../auth.service'; 
import { UploadService } from '../old/upload.service';
import { HttpClient , HttpHeaders} from '@angular/common/http';

@Component({
  selector: 'app-nav',
  templateUrl: './nav.component.html',
  styleUrls: ['./../app.component.css']
})

export class NavComponent implements OnInit {

  constructor(private http: HttpClient, private authService:AuthService, private uploadService: UploadService) {
    }
 
  Okshare = false;
  Notshare = false;
  Okdelete = false;
  UserNotshare = false;
  Nodelete = false;
  Noupload = false;
  Okdeleteall = false;
  NoNoupload = false;
  Okupload = false;
  fileName: string = '';
  username= localStorage.getItem('username');
  fileList: any;

  postData = {
    text: '',
    image: null as File | null  // Se inicializa como null y se asignará al seleccionar un archivo
  };

  ngOnInit(): void {

  }

  logout(): void { // CERRRA SESIÓN
    this.authService.setAuthenticated(false);
    localStorage.removeItem('username');
    localStorage.removeItem('auth');
  }

  nombre(event: any) { // OBTENER NOMBRE 
    let input = event.target;
    this.fileName = input.files[0].name;
    this.upload(event)
    setTimeout(() => {
      this.fileName= '';
      //this.getAllPosts();
    }, 1000);
  }

  upload(event: any) {
    const file = event.target.files && event.target.files[0];
    this.uploadService.upload(file, this.username).subscribe(
      response => {
        //console.log(response.code)
        if (response.code==201) { // Archivo subido correctamente
          this.fileList = response.files;
          this.Okupload = true;         
          setTimeout(() => {
          }, 500); 
          setTimeout(() => {
            this.Okupload = false;
          }, 3000);
        }
        if (response.code==401) { // Error al subir el archivo
          this.Noupload = true;          
          setTimeout(() => {
            this.Noupload = false;
          }, 3000);
        } 
        if (response.code==402) { // No puedes subir un archivo con el mismo nombre
          this.NoNoupload = true;          
          setTimeout(() => {
            this.NoNoupload = false;
          }, 3000);
        } 
      },
      error => {
        //console.log(error.status)
        if (error.status==401) { // Error al subir el archivo
          this.Noupload = true;          
          setTimeout(() => {
            this.Noupload = false;
          }, 3000);
        } 
        if (error.status==200) { // No puedes subir un archivo con el mismo nombre
          this.NoNoupload = true;          
          setTimeout(() => {
            this.NoNoupload = false;
          }, 3000);
        } 
        //console.error('Error al hacer la solicitud:', error.error);
      }
    );
  }

  onSubmit() {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/post_image.php`;
    const body = { 
      id_user: '1', 
      text: this.postData.text, 
      url_image: this.postData.text
    };

  this.http.post(url, JSON.stringify(body), httpOptions)
    .subscribe(
      (data: any) => {
        this.Okdeleteall = true;  
        setTimeout(() => {
          this.Okdeleteall =  false;
        }, 3000);
        setTimeout(() => {
          //this.getPosts();
        }, 500);
      },
      (error: any) => {
        this.Okdeleteall = true;  
        setTimeout(() => {
          this.Okdeleteall =  false;
        }, 3000);
        setTimeout(() => {
          //this.getPosts();
        }, 500);
      }
    );
  }

  onFileSelected(event: any) {
    const file: File = event.target.files[0];
    this.postData.image = file;
  }
  
}