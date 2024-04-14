import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class Get_my_postsService {
  private apiUrl = 'https://das-uabook.000webhostapp.com/get_my_posts.php';
  
  constructor(private http: HttpClient) {}

  get_my_posts(username: any): Observable<any> {
    const formData = new FormData();
    const token = localStorage.getItem('token');
    if(token!=null){
      formData.append('token', token);
    }
    formData.append('username', username);
    return this.http.post<any>(this.apiUrl, formData);
  }
}