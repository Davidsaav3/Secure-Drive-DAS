import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { catchError } from 'rxjs/operators';
import { throwError } from 'rxjs';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../auth.service'; 
import { DownloaderService } from '../old/downloader.service';
import { DownloaderSharedService } from '../old/downloaderShared.service';
import { UploadService } from '../old/upload.service';
import { Get_all_postsService } from '../get_all_posts.service';
import { MyfilesService } from '../old/myshare.service';
import { OtherfilesService } from '../old/othershare.service';
import { DomSanitizer, SafeUrl } from '@angular/platform-browser';
import { DownService } from '../old/down.service';
import { Router } from '@angular/router';
import { Pipe, PipeTransform } from '@angular/core';

@Pipe({ name: 'replace' })
export class ReplacePipe implements PipeTransform {
  transform(value: string, find: string, replacement: string): string {
    return value.replace(new RegExp(find, 'g'), replacement);
  }
}

@Component({
  selector: 'app-inicio',
  templateUrl: './inicio.component.html',
  styleUrls: ['./../app.component.css']
})

export class InicioComponent implements OnInit {

  constructor(private downloaderShared: DownloaderSharedService, private downService: DownService, private sanitizer: DomSanitizer, private get_all_postsService: Get_all_postsService,private uploadService: UploadService, private downloaderService: DownloaderService, private authService:AuthService, private formBuilder: FormBuilder, private http: HttpClient, private myfilesService: MyfilesService, private otherfilesService: OtherfilesService, private router: Router) {
  }
 
  files: any[] = [];
  selectedFile: File | null = null;
  fileName: string = '';
  email: any;
  pag= 0;

  username= localStorage.getItem('username');
  id= localStorage.getItem('id');
  id_post= 1;

  archivos: any[] = [];
  archivos2: any[] = [];
  archivos3: any[] = [];
  fileList: any;
  options: string[] = [];
  mostrarDiv: any;

  Okshare = false;
  Notshare = false;
  Okdelete = false;
  UserNotshare = false;
  Nodelete = false;
  Noupload = false;
  Okdeleteall = false;
  NoNoupload = false;
  Okupload = false;

  imageUrl1: string[] = [];
  folderPath = '/imagenes'; // Reemplaza con la ruta de tus imágenes
  images: any[] = [];
  shareForm: FormGroup = this.formBuilder.group({
    username: ['', [Validators.required]],
  });

  postData = {
    text: '',
    image: null as File | null  // Se inicializa como null y se asignará al seleccionar un archivo
  };

  posts = {
    post:[
      {
        id_post: 0,
        id_user: 0,
        text_post: '',
        url_image: '',
        date: '',
        likes: 0,
        username: '',
        comments: [
          {
            id: 0,
            username: "",
            text: ""
          }
        ],
      }
    ],
  };

  ngOnInit(): void { // INICIALIZACIÓN
    if (localStorage.getItem('username')==null) {
      //this.router.navigate(['login']);
    }
    this.getAllPosts();
  }

  toggleDiv() { // MOSTRAR
    this.mostrarDiv = !this.mostrarDiv;
  }

  getAllPosts() {  // OBTENER PUBLICACIONES
    const id_user = 1; 
    this.get_all_postsService.get_all_posts(id_user).subscribe(
      (response: any) => {
        console.log(response)
        this.posts.post = response;
      },
      (error: any) => {
        console.error('Error al obtener la lista de archivos:', error);
      }
    );
  }  

  deletePost() { // ELIMINA UNA PUBLICACIÓN
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/delete_post.php`;
    const body = { 
      id: this.id_post,
      id_user: this.id, 
    };

  this.http.post(url, JSON.stringify(body), httpOptions)
    .subscribe(
      (data: any) => {
        this.Okdeleteall = true;  
        setTimeout(() => {
          this.Okdeleteall =  false;
        }, 3000);
        setTimeout(() => {
          this.getAllPosts();
        }, 500);
      },
      (error: any) => {
        this.Okdeleteall = true;  
        setTimeout(() => {
          this.Okdeleteall =  false;
        }, 3000);
        setTimeout(() => {
          this.getAllPosts();
        }, 500);
      }
    );
  }
  
  postLike(id_post: any) { // DAR LIKE
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/post_like.php`;
    const body = { 
      id: id_post, 
      id_user: this.id, 
    };

  this.http.post(url, JSON.stringify(body), httpOptions)
    .subscribe(
      (data: any) => {
        setTimeout(() => {
          this.getAllPosts();
        }, 500);
      },
      (error: any) => {
        setTimeout(() => {
          this.getAllPosts();
        }, 500);
      }
    );
  }

  postComment(id_post: any, text: any){ // COMENTAR POST
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/post_comment.php`;
    const body = { 
      id_post: id_post, 
      id_user: this.id, 
      text: text
    };

  this.http.post(url, JSON.stringify(body), httpOptions)
    .subscribe(
      (data: any) => {
        setTimeout(() => {
          this.getAllPosts();
        }, 500);
      },
      (error: any) => {
        setTimeout(() => {
          this.getAllPosts();
        }, 500);
      }
    );
  }

  editPost() { // EDITAR PERFIL
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/edit_post.php`;
    const body = { 
      id: this.id_post,
      text: this.postData.text, 
      url_image: this.postData.image
    };

  this.http.post(url, JSON.stringify(body), httpOptions)
    .subscribe(
      (data: any) => {
        setTimeout(() => {
          this.getAllPosts();
        }, 500);
      },
      (error: any) => {
        setTimeout(() => {
          this.getAllPosts();
        }, 500);
      }
    );
  }

  onFileSelected(event: any) {
    const file: File = event.target.files[0];
    this.postData.image = file;
  }


}