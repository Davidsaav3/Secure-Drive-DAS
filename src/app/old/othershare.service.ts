import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class OtherfilesService {
  private apiUrl = 'https://dasapp.alwaysdata.net/sharedwithme.php';
  
  constructor(private http: HttpClient) {}

  files(folderPath: string, username: string): Observable<any> {
    const formData = new FormData();
    formData.append('folder_path', folderPath);
    formData.append('username', username);
    return this.http.post<any>(this.apiUrl, formData);
  }
}