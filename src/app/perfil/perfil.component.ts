import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../auth.service'; 
import { Router, ActivatedRoute } from '@angular/router';
import { Pipe, PipeTransform } from '@angular/core';
import { DomSanitizer, SafeUrl } from '@angular/platform-browser';

import { Delete_postService } from '../delete_post.service';
import { Edit_postService } from '../edit_post.service';
import { Edit_profileService } from '../edit_profile.service';
import { Get_my_postsService } from '../get_my_posts.service';
import { Get_profileService } from '../get_profile.service';
import { Post_commentService } from '../post_comment.service';
import { Post_imageService } from '../post_image.service';
import { Post_likeService } from '../post_like.service';
import { Post_requestService } from '../post_request.service';
import { Resolve_requestService } from '../resolve_request.service';

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

  constructor(private get_profileService: Get_profileService, private resolve_requestService: Resolve_requestService, private post_requestService: Post_requestService, private post_likeService: Post_likeService, private post_imageService: Post_imageService, private post_commentService: Post_commentService, private get_my_postsService : Get_my_postsService, private edit_profileService: Edit_profileService, private edit_postService: Edit_postService, private delete_postService: Delete_postService, private route: ActivatedRoute, private sanitizer: DomSanitizer, private authService:AuthService, private formBuilder: FormBuilder, private http: HttpClient, private router: Router) {
  }
 
  files: any[] = [];
  fileName: string = '';
  email: any;
  pag= 0;
  username= localStorage.getItem('username');
  profilename: String | undefined;
  id= 'p2.txt';
  archivos: any[] = [];
  archivos2: any[] = [];
  fileList: any;
  options: string[] = [];

  profile= true;
  follow= true;
  numfollow= 0;
  numfollowers= 0;
  numimages= 0;
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
  imageName = 'tu-imagen.jpg'; // Reemplaza con el nombre de tu imagen
  imageUrl1: string[] = [];
  folderPath = '/imagenes'; // Reemplaza con la ruta de tus imágenes
  images: any[] = [];

  shareForm: FormGroup = this.formBuilder.group({
    username: ['', [Validators.required]],
  });

  user = {
    id: 0,
    username: "",
    following: 0,
    followers: 0,
    num_posts: 0,
    status: true,
    requests: [
      {
        id_user: 0,
        username: "Davidsaav3"
      }
    ],
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

  postData = {
    text: '',
    image: null as File | null  // Se inicializa como null y se asignará al seleccionar un archivo
  };

  ngOnInit(): void { // INICILIZACIÓN
    this.getUser()
    this.getPosts()

    this.profilename = this.route.snapshot.url[1].path;
    if (localStorage.getItem('username')==null) {
      //this.router.navigate(['login']);
    }
    //this.getPosts();
  }

  toggleDiv() {
    this.mostrarDiv = !this.mostrarDiv;
  }

  getUser(){ // OBTIENE DATOS DE USUARIO
    const id_user = 1; 
    this.get_profileService.get_profile(id_user).subscribe(
      (response: any) => {
        console.log(response)
        this.user = response;
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

  getPosts() {  // OBTENER PUBLICACIONES
    const id_user = 1; 
    this.get_my_postsService.get_my_posts(id_user).subscribe(
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

  /*down(id :string): void { // DESCARGAR IMAGENES
    this.downService.getFileView(id).subscribe(
      (url: string) => {
        return url;
        this.imageUrl1.push(url);
      },
      (error: any) => {
        //console.error('Error al obtener la imagen:', error);
      }
    );
  }*/

  getPreImage(image: any): SafeUrl { // PREVISUALIZACIÓN IMAGENES
    const base64Image = image.contenido;
    const imageUrl = 'data:image/' + image.tipo + ';base64,' + base64Image;
    return this.sanitizer.bypassSecurityTrustUrl(imageUrl);
  }

  getUsername(event: any) { // OBTENER NOMBRE DE USUARIO ACTUAL
    let input = event.target;
    this.fileName = input.files[0].name;
    setTimeout(() => {
      this.fileName= '';
      this.getPosts();
    }, 1000);
  }

  detelePost(id: string) { // ELIMINA UNA PUBLICACIÓN
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/delete_post.php`;
    const body = { 
      id: '3', 
      id_user: '2', 
    };

  this.http.post(url, JSON.stringify(body), httpOptions)
    .subscribe(
      (data: any) => {
        this.Okdeleteall = true;  
        setTimeout(() => {
          this.Okdeleteall =  false;
        }, 3000);
        setTimeout(() => {
          this.getPosts();
        }, 500);
      },
      (error: any) => {
        this.Okdeleteall = true;  
        setTimeout(() => {
          this.Okdeleteall =  false;
        }, 3000);
        setTimeout(() => {
          this.getPosts();
        }, 500);
      }
    );
  }

  downloadPost(id: any) { // DESCARGAR IMAGEN
    //this.downloaderService.downloader(this.username+'/'+id);
  }

  downloadPostExt(id: any, owner: any) { // DESCARGAR COMPARTIDA
    //this.downloaderShared.downloader(owner+'/'+id, owner, this.username);
  }
  
  postLike(idUser: any, idPost: any) { // DAR LIKE
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/post_like.php`;
    const body = { 
      id: '3', 
      id_user: '2', 
    };

  this.http.post(url, JSON.stringify(body), httpOptions)
    .subscribe(
      (data: any) => {
        this.Okdeleteall = true;  
        setTimeout(() => {
          this.Okdeleteall =  false;
        }, 3000);
        setTimeout(() => {
          this.getPosts();
        }, 500);
      },
      (error: any) => {
        this.Okdeleteall = true;  
        setTimeout(() => {
          this.Okdeleteall =  false;
        }, 3000);
        setTimeout(() => {
          this.getPosts();
        }, 500);
      }
    );
  }

  postComment(idUser: any, idPost: any, text: any){ // COMENTAR POST
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/post_comment.php`;
    const body = { 
      id_post: '3', 
      id_user: '2', 
      text: 'hola'
    };

  this.http.post(url, JSON.stringify(body), httpOptions)
    .subscribe(
      (data: any) => {
        this.Okdeleteall = true;  
        setTimeout(() => {
          this.Okdeleteall =  false;
        }, 3000);
        setTimeout(() => {
          this.getPosts();
        }, 500);
      },
      (error: any) => {
        this.Okdeleteall = true;  
        setTimeout(() => {
          this.Okdeleteall =  false;
        }, 3000);
        setTimeout(() => {
          this.getPosts();
        }, 500);
      }
    );
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


  PostRequest(){ // ACEPTAR SOLICITUD
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/post_request.php`;
    const body = { 
      id_sender: 1,
      id_receiver: 2,
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

  ResolveRequest(state: any){
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/resolve_request.php`;
    const body = { 
      id_sender: 1,
      id_receiver: 2,
      status: state
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

  onSubmit2() {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/edit_post.php`;
    const body = { 
      id: '1', 
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