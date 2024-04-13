import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class Post_imageilesService {
  private apiUrl = 'https://das-uabook.000webhostapp.com/post_image.php';
  
  constructor(private http: HttpClient) {}

  post_image(id_user: any): Observable<any> {
    const formData = new FormData();
    formData.append('id_user', id_user);
    return this.http.post<any>(this.apiUrl, formData);
  }
}