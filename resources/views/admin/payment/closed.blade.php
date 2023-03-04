<x-admin-layout>
    <div class="row">
        <div class="col-md-3 h-100">
            @include('admin.payment.partials.sidebar')
        </div>
        <div class="col-md-9">
            <div class="page-title d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                @include('admin.payment.partials.header', ['title'=>'Tamamlanmış Ödemeler'])
                <div class="input-group input-group-sm mb-1 w-25">
                    <span class="input-group-text rounded-0 bg-transparent" id="search-form"><i class="ri-search-2-line"></i></span>
                    <input type="search" id="tableSearch" class="form-control border-start-0 rounded-0 shadow-none" placeholder="İçerik" aria-label="İçerik" aria-describedby="search-form">
                </div>
            </div>
            <div class="page-content">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Hesap Bilgileri</th>
                                <th>Ödeme Tarihi</th>
                                <th>Ödeme Dönemi</th>
                                <th>Ödeme Tutarı</th>
                            </tr>
                        </thead>
                        <tbody id="content-list">
                            <tr>
                                <td>1</td>
                                <td>
                                    <div class="d-block mb-1">Herkobi Dijital Çözümler Sanayi ve Ticaret A.Ş.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>
                                    <div class="d-block mb-1">Turown Endüstriyel Sanayi ve Ticaret Ltd. Şti.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>
                                    <div class="d-block mb-1">BS İnternet Yazılım Reklam Pazarlama ve Danışmanlık Hizmetleri</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>
                                    <div class="d-block mb-1">Herkobi Dijital Çözümler Sanayi ve Ticaret A.Ş.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td>
                                    <div class="d-block mb-1">Turown Endüstriyel Sanayi ve Ticaret Ltd. Şti.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>6</td>
                                <td>
                                    <div class="d-block mb-1">BS İnternet Yazılım Reklam Pazarlama ve Danışmanlık Hizmetleri</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>7</td>
                                <td>
                                    <div class="d-block mb-1">Herkobi Dijital Çözümler Sanayi ve Ticaret A.Ş.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>8</td>
                                <td>
                                    <div class="d-block mb-1">Turown Endüstriyel Sanayi ve Ticaret Ltd. Şti.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>9</td>
                                <td>
                                    <div class="d-block mb-1">BS İnternet Yazılım Reklam Pazarlama ve Danışmanlık Hizmetleri</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>10</td>
                                <td>
                                    <div class="d-block mb-1">Herkobi Dijital Çözümler Sanayi ve Ticaret A.Ş.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>11</td>
                                <td>
                                    <div class="d-block mb-1">Turown Endüstriyel Sanayi ve Ticaret Ltd. Şti.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>12</td>
                                <td>
                                    <div class="d-block mb-1">BS İnternet Yazılım Reklam Pazarlama ve Danışmanlık Hizmetleri</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>13</td>
                                <td>
                                    <div class="d-block mb-1">Herkobi Dijital Çözümler Sanayi ve Ticaret A.Ş.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>14</td>
                                <td>
                                    <div class="d-block mb-1">Turown Endüstriyel Sanayi ve Ticaret Ltd. Şti.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>15</td>
                                <td>
                                    <div class="d-block mb-1">BS İnternet Yazılım Reklam Pazarlama ve Danışmanlık Hizmetleri</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>16</td>
                                <td>
                                    <div class="d-block mb-1">Herkobi Dijital Çözümler Sanayi ve Ticaret A.Ş.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>17</td>
                                <td>
                                    <div class="d-block mb-1">Turown Endüstriyel Sanayi ve Ticaret Ltd. Şti.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>18</td>
                                <td>
                                    <div class="d-block mb-1">BS İnternet Yazılım Reklam Pazarlama ve Danışmanlık Hizmetleri</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>19</td>
                                <td>
                                    <div class="d-block mb-1">Herkobi Dijital Çözümler Sanayi ve Ticaret A.Ş.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>20</td>
                                <td>
                                    <div class="d-block mb-1">Turown Endüstriyel Sanayi ve Ticaret Ltd. Şti.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>21</td>
                                <td>
                                    <div class="d-block mb-1">Turown Endüstriyel Sanayi ve Ticaret Ltd. Şti.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>22</td>
                                <td>
                                    <div class="d-block mb-1">Turown Endüstriyel Sanayi ve Ticaret Ltd. Şti.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>23</td>
                                <td>
                                    <div class="d-block mb-1">Turown Endüstriyel Sanayi ve Ticaret Ltd. Şti.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>24</td>
                                <td>
                                    <div class="d-block mb-1">Turown Endüstriyel Sanayi ve Ticaret Ltd. Şti.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                            <tr>
                                <td>25</td>
                                <td>
                                    <div class="d-block mb-1">Turown Endüstriyel Sanayi ve Ticaret Ltd. Şti.</div>
                                    <div class="d-block small"><span>Kullanılan Paket:</span> Standart</div>
                                </td>
                                <td>15.08.2023</td>
                                <td>15.08.2023</td>
                                <td>₺ 250,00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-end">
                        <li class="page-item">
                            <a class="page-link rounded-0 text-black" href="#" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item"><a class="page-link text-black" href="#">1</a></li>
                        <li class="page-item"><a class="page-link text-black" href="#">2</a></li>
                        <li class="page-item"><a class="page-link text-black" href="#">3</a></li>
                        <li class="page-item"><a class="page-link text-black" href="#">4</a></li>
                        <li class="page-item"><a class="page-link text-black" href="#">5</a></li>
                        <li class="page-item"><a class="page-link text-black" href="#">6</a></li>
                        <li class="page-item">
                            <a class="page-link rounded-0 text-black" href="#" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</x-admin-layout>
