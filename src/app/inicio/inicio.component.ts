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
  id= 'p2.txt';
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

  ngOnInit(): void {
    if (localStorage.getItem('username')==null) {
      //this.router.navigate(['login']);
    }
    this.getAllPosts();
  }

  toggleDiv() {
    this.mostrarDiv = !this.mostrarDiv;
  }

  getAllPosts() {  // OBTENER PUBLICACIONES
    const id_user = 1; 
    this.get_all_postsService.get_all_posts(id_user).subscribe(
      (response: any) => {
        console.log(response)
        this.posts.post = response;
        /*for (let i = 0; i < this.archivos.length; i++) {
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
        }*/
      },
      (error: any) => {
        console.error('Error al obtener la lista de archivos:', error);
      }
    );
  }  

  down(id :string): void { // PREVISUALIZAIÓN DE IMAGENES
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

  eliminar(id: string) { // ELIMIANR POST
    const url = `https://dasapp.alwaysdata.net/files.php`;
    const httpOptions = {
      headers: new HttpHeaders({ 'Content-Type': 'application/json' })
    };
    const formData = new FormData();
    formData.append('file_name', this.username+'/'+id);

      const headers = new HttpHeaders();
      this.http.post('https://dasapp.alwaysdata.net/delete.php', formData, { headers })
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
            //console.error('Error al subir el archivo:', error);
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

  descargar(id: any) { // DESCARGAR POST
    this.downloaderService.downloader(this.username+'/'+id);
  }

  descargarShared(id: any, owner: any) { // DESCARGAR POST DE OTRO USUARIO
    this.downloaderShared.downloader(owner+'/'+id, owner, this.username);
  }

  getSafeImageUrl(image: any): SafeUrl { // URL IMAGEN
    const base64Image = image.contenido;
    const imageUrl = 'data:image/' + image.tipo + ';base64,' + base64Image;
    return this.sanitizer.bypassSecurityTrustUrl(imageUrl);
  }

  postLike(idUser: any, idPost: any) { // DAR LIKE
    const url = "https://dasapp.alwaysdata.net/postlike";
    const data = {
      idUser: 0,
      idPost: 0
    };
    this.http.post(url, data).subscribe((respuesta) => {
      console.log("Respuesta:", respuesta);
    }, (error) => {
      console.log("Error:", error);
    });
  }

  postComment(idUser: any, idPost: any, text: any){ // COMENTAR POST
    const url = "https://dasapp.alwaysdata.net/postreqqest";
    const data = {
      idUser: 0,
      idPost: 0,
      text: "Contenido comentario"
    };
    this.http.post(url, data).subscribe((respuesta) => {
      console.log("Respuesta:", respuesta);
    }, (error) => {
      console.log("Error:", error);
    });
  }

  editPost(idPost: any, name: any){ // EDITAR POST
    const url = "https://dasapp.alwaysdata.net/editpost";
    const data = {
      idPost: 0,
      name: "imagen"
    };
    this.http.post(url, data).subscribe((respuesta) => {
      console.log("Respuesta:", respuesta);
    }, (error) => {
      console.log("Error:", error);
    });
  }

  resolveReqqest(idUser: any, idUser2: any){ // ACEPTAR O DENEGAR / SOLICCITUD
    const url = "https://dasapp.alwaysdata.net/resolvereqqest";
    const data = {
      idUser: 0,
      idUser2: 0
    };
    this.http.post(url, data).subscribe((respuesta) => {
      console.log("Respuesta:", respuesta);
    }, (error) => {
      console.log("Error:", error);
    });
  }

  postRequest(idUser: any, idUser2: any){ // SEGUIR USUARIO
    const url = "https://dasapp.alwaysdata.net/postreqqest";
    const data = {
      idUser: 0,
      idUser2: 0
    };
    this.http.post(url, data).subscribe((respuesta) => {
      console.log("Respuesta:", respuesta);
    }, (error) => {
      console.log("Error:", error);
    });
  }

  postPublicPrivateProfile(id: any, option: any){ // ACEPTAR SOLICITUD
    const url = "https://dasapp.alwaysdata.net/resolvereqqest";
    const data = {
      id: 0,
      option: true,
    };
    this.http.post(url, data).subscribe((respuesta) => {
      console.log("Respuesta:", respuesta);
    }, (error) => {
      console.log("Error:", error);
    });
  }
  
  login2() {
    const apiUrl = 'https://dasapp.alwaysdata.net/login';

    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Methods': 'POST, OPTIONS',
        'Access-Control-Allow-Headers': 'Content-Type'
      })
    };

    this.http.post(apiUrl, { username: "david", password: "123" }, httpOptions)
      .subscribe(
        (response: any) => {
          console.log('Inicio de sesión exitoso');
        },
        (error) => {
          console.error('Error al iniciar sesión:', error);
        }
      );
  }

}