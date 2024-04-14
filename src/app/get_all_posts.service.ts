import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class Get_all_postsService {
  private apiUrl = 'https://das-uabook.000webhostapp.com/get_all_posts.php';
  
  constructor(private http: HttpClient) {}

  get_all_posts(id_user: any): Observable<any> {
    const formData = new FormData();
    formData.append('id_user', id_user);
    const token = localStorage.getItem('token');
    if(token!=null){
      formData.append('token', token);
    }
    return this.http.post<any>(this.apiUrl, formData);
  }
}