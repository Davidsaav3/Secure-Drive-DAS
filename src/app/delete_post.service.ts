import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class Delete_postService {
  private apiUrl = 'https://das-uabook.000webhostapp.com/delete_post.php';
  
  constructor(private http: HttpClient) {}

  delete_post(id_user: any): Observable<any> {
    const formData = new FormData();
    formData.append('id_user', id_user);
    return this.http.post<any>(this.apiUrl, formData);
  }
}