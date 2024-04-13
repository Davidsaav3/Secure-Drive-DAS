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
    image: null as File | null  
  };

  ngOnInit(): void {

  }

  logout(): void { // CERRRA SESIÓN
    this.authService.setAuthenticated(false);
    localStorage.removeItem('username');
    localStorage.removeItem('id');
    localStorage.removeItem('auth');
  }

  nombre(event: any) { // OBTENER NOMBRE 
    let input = event.target;
    this.fileName = input.files[0].name;
    //this.upload(event)
    setTimeout(() => {
      this.fileName= '';
      //this.getAllPosts();
    }, 1000);
  }

  PostImage() { // PUBLICAR IMAGEN
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
        this.Okupload = true;  
        setTimeout(() => {
          this.Okupload =  false;
        }, 3000);
        setTimeout(() => {
          //this.getPosts();
        }, 500);
      },
      (error: any) => {
        this.NoNoupload = true;  
        setTimeout(() => {
          this.NoNoupload =  false;
        }, 3000);
        setTimeout(() => {
          //this.getPosts();
        }, 500);
      }
    );
  }

  onFileSelected(event: any) { // CARGA IMAGENES
    const file: File = event.target.files[0];
    this.postData.image = file;
  }
  
}